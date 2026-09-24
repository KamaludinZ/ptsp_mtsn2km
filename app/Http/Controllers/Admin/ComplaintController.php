<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $query = Complaint::query();
        
        // Exclude whistleblowing reports from main complaints page
        $query->where(function($q) {
            $q->where('is_whistleblowing', false)
              ->orWhere('is_whistleblowing', null)
              ->orWhere('complaint_type', '!=', 'whistleblowing')
              ->orWhereRaw('complaint_type IS NULL');
        });
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('subject', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('reporter_name', 'like', '%' . $request->search . '%')
                  ->orWhere('reporter_email', 'like', '%' . $request->search . '%');
            });
        }

        $complaints = $query->with(['service', 'assignedTo'])->latest()->paginate(15);
        $services = Service::all();
        
        return view('admin.complaints.index', compact('complaints', 'services'));
    }

    public function create()
    {
        $services = Service::all();
        $users = User::all();
        return view('admin.complaints.create', compact('services', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'complaint_number' => 'required|string|max:255|unique:complaints',
            'type' => 'required|in:pengaduan,saran',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'reporter_name' => 'nullable|string|max:255',
            'reporter_email' => 'nullable|email|max:255',
            'reporter_phone' => 'nullable|string|max:255',
            'service_id' => 'nullable|exists:services,id',
            'category' => 'nullable|in:pelayanan,pegawai,fasilitas,prosedur,lainnya',
            'status' => 'required|in:pending,in_review,resolved,closed',
            'priority' => 'required|in:low,normal,high,urgent',
            'assigned_to_id' => 'nullable|exists:users,id',
            'is_whistleblowing' => 'boolean',
        ]);

        Complaint::create($request->all());

        return redirect()->route('admin.complaints.index')->with('success', 'Pengaduan berhasil ditambahkan.');
    }

    public function show(Complaint $complaint)
    {
        $complaint->load(['service', 'assignedTo']);
        return view('admin.complaints.show', compact('complaint'));
    }

    public function edit(Complaint $complaint)
    {
        $services = Service::all();
        $users = User::all();
        return view('admin.complaints.edit', compact('complaint', 'services', 'users'));
    }

    public function update(Request $request, Complaint $complaint)
    {
        $request->validate([
            'complaint_number' => 'required|string|max:255|unique:complaints,complaint_number,' . $complaint->id,
            'type' => 'required|in:pengaduan,saran',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'reporter_name' => 'nullable|string|max:255',
            'reporter_email' => 'nullable|email|max:255',
            'reporter_phone' => 'nullable|string|max:255',
            'service_id' => 'nullable|exists:services,id',
            'category' => 'nullable|in:pelayanan,pegawai,fasilitas,prosedur,lainnya',
            'status' => 'required|in:pending,in_review,resolved,closed',
            'priority' => 'required|in:low,normal,high,urgent',
            'assigned_to_id' => 'nullable|exists:users,id',
            'response' => 'nullable|string',
            'is_whistleblowing' => 'boolean',
        ]);

        $complaint->update($request->all());

        return redirect()->route('admin.complaints.index')->with('success', 'Pengaduan berhasil diperbarui.');
    }

    public function destroy(Complaint $complaint)
    {
        $complaint->delete();

        return redirect()->route('admin.complaints.index')->with('success', 'Pengaduan berhasil dihapus.');
    }

    /**
     * Display whistleblowing reports
     */
    public function whistleblowingIndex()
    {
        $this->authorize('viewAny', Complaint::class);
        
        $query = Complaint::where(function($q) {
            $q->where('complaint_type', 'whistleblowing')
              ->orWhere('is_whistleblowing', true);
        });
        
        $whistleblowingReports = $query->with('user', 'assignee')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.complaints.whistleblowing.index', compact('whistleblowingReports'));
    }

    /**
     * Display a specific whistleblowing report
     */
    public function whistleblowingShow(Complaint $complaint)
    {
        // Verify it's a whistleblowing report
        if ($complaint->complaint_type !== 'whistleblowing') {
            abort(404);
        }
        
        $this->authorize('view', $complaint);
        
        // Load related data
        $complaint->load(['user', 'assignee', 'replies.user']);

        return view('admin.complaints.whistleblowing.show', compact('complaint'));
    }

    /**
     * Download an evidence file attached to a complaint/whistleblowing report.
     * Files are stored on the private disk; only authenticated admin staff
     * (already gated by the route's role:admin middleware) may reach this.
     */
    public function downloadEvidence(Complaint $complaint, int $index)
    {
        $this->authorize('view', $complaint);

        $files = json_decode($complaint->evidence_files ?? '[]', true) ?? [];

        if (!isset($files[$index])) {
            abort(404);
        }

        return \Illuminate\Support\Facades\Storage::disk('local')->response($files[$index]);
    }

    /**
     * Update whistleblowing report status
     */
    public function updateWhistleblowingStatus(Request $request, Complaint $complaint)
    {
        // Verify it's a whistleblowing report
        if ($complaint->complaint_type !== 'whistleblowing') {
            abort(404);
        }
        
        $this->authorize('update', $complaint);
        
        $request->validate([
            'status' => 'required|in:pending,processing,completed,rejected',
            'response_notes' => 'nullable|string',
        ]);

        $complaint->update([
            'status' => $request->status,
            'response_notes' => $request->response_notes,
            'closed_at' => $request->status === 'completed' ? now() : null,
        ]);

        return redirect()->route('admin.whistleblowing.index')->with('success', 'Status whistleblowing berhasil diperbarui.');
    }
}