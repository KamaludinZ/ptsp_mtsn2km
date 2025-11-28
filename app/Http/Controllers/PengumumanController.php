<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use App\Models\PengumumanView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengumumanController extends Controller
{
    /**
     * Display a listing of the resource for public.
     */
    public function index(Request $request)
    {
        $query = Pengumuman::where('is_active', true)
                    ->where('publish_date', '<=', now())
                    ->where(function($query) {
                        $query->whereNull('end_date')
                              ->orWhere('end_date', '>=', now());
                    });

        // Handle search
        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('content', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('author', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Handle category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Handle date range filter - validate dates are in correct format
        if ($request->filled('start_date')) {
            $startDate = $request->start_date;
            // Validate date format
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
                $query->where('publish_date', '>=', $startDate);
            }
        }

        if ($request->filled('end_date')) {
            $endDate = $request->end_date;
            // Validate date format
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
                $query->where('publish_date', '<=', $endDate);
            }
        }

        // Additional validation: ensure end date is not before start date
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            // Only apply date range if start date is not after end date
            if (strtotime($startDate) <= strtotime($endDate)) {
                $query->where('publish_date', '>=', $startDate)
                      ->where('publish_date', '<=', $endDate);
            }
        }

        $pengumumen = $query->orderBy('publish_date', 'desc')
                    ->paginate(10)
                    ->appends($request->query());

        return view('pengumuman.index', compact('pengumumen'));
    }

    /**
     * Display a listing of all announcements for admin.
     */
    public function adminIndex()
    {
        $this->authorizeAdmin();
        
        $pengumumen = Pengumuman::orderBy('publish_date', 'desc')->paginate(10);
        
        return view('pengumuman.admin.index', compact('pengumumen'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorizeAdmin();
        
        return view('pengumuman.admin.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();
        
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'category' => 'nullable|string|max:100',
            'publish_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:publish_date',
            'is_active' => 'boolean',
            'author' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // max 10MB
            'url' => 'nullable|url',
        ]);

        $user = Auth::user();
        $pengumuman = new Pengumuman();
        $pengumuman->fill($request->all());
        
        // Handle file upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('pengumuman_attachments', $filename, 'public');
            $pengumuman->attachment = $path;
        }
        
        $pengumuman->user_id = $user->id;
        $pengumuman->author = $pengumuman->author ?? $user->name;
        $pengumuman->save();

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil ditambahkan.');
    }

    /**
     * Display the specified resource for admin.
     */
    public function show(Pengumuman $pengumuman)
    {
        // For public users - only show active announcements
        if (!Auth::check() ||
            !Auth::user()->hasAnyRole(['admin', 'super_admin'])) {
            // Check if the announcement is active and within the valid date range
            if (!$pengumuman->is_active ||
                $pengumuman->publish_date > now() ||
                ($pengumuman->end_date && $pengumuman->end_date < now())) {
                abort(404);
            }
        }

        // Get the client IP address
        $ipAddress = request()->ip();

        // Check if this IP has already viewed this announcement
        $existingView = PengumumanView::where('pengumuman_id', $pengumuman->id)
                                    ->where('ip_address', $ipAddress)
                                    ->first();

        // If this is a new unique view, increment the count and record it
        if (!$existingView) {
            $pengumuman->increment('view_count');

            // Record the view to prevent duplicate counting from the same IP
            PengumumanView::create([
                'pengumuman_id' => $pengumuman->id,
                'ip_address' => $ipAddress,
            ]);
        }

        return view('pengumuman.show', compact('pengumuman'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pengumuman $pengumuman)
    {
        $this->authorizeAdmin();
        
        return view('pengumuman.admin.edit', compact('pengumuman'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pengumuman $pengumuman)
    {
        $this->authorizeAdmin();
        
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'category' => 'nullable|string|max:100',
            'publish_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:publish_date',
            'is_active' => 'boolean',
            'author' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // max 10MB
            'url' => 'nullable|url',
        ]);

        $oldAttachment = $pengumuman->attachment;
        
        $pengumuman->fill($request->all());
        
        // Handle file upload
        if ($request->hasFile('attachment')) {
            // Delete old file if exists
            if ($oldAttachment && file_exists(storage_path('app/public/' . $oldAttachment))) {
                unlink(storage_path('app/public/' . $oldAttachment));
            }
            
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('pengumuman_attachments', $filename, 'public');
            $pengumuman->attachment = $path;
        }
        
        $pengumuman->save();

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pengumuman $pengumuman)
    {
        $this->authorizeAdmin();
        
        $pengumuman->delete();

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil dihapus.');
    }

    /**
     * Authorize admin access for CRUD operations
     */
    private function authorizeAdmin()
    {
        if (!Auth::check() || !Auth::user()->hasAnyRole(['admin', 'super_admin'])) {
            abort(403, 'Akses ditolak. Hanya admin yang dapat mengakses fitur ini.');
        }
    }
}
