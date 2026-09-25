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
                $q->where('title', 'ILIKE', "%{$searchTerm}%")
                  ->orWhere('content', 'ILIKE', "%{$searchTerm}%")
                  ->orWhere('author', 'ILIKE', "%{$searchTerm}%");
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
     * Display the specified resource for admin.
     */
    public function show(Pengumuman $pengumuman)
    {
        // For public users - only show active announcements
        if (!Auth::check() ||
            !Auth::user()->hasRole('admin')) {
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
     * Authorize admin access for CRUD operations
     */
    private function authorizeAdmin()
    {
        if (!Auth::check() || !Auth::user()->hasRole('admin')) {
            abort(403, 'Akses ditolak. Hanya admin yang dapat mengakses fitur ini.');
        }
    }
}
