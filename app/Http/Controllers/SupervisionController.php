<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SupervisionController extends Controller
{
    /**
     * Complaint dashboard
     */
    public function complaints()
    {
        $stats = [
            'total_complaints' => Complaint::count(),
            'pending_complaints' => Complaint::where('status', 'pending')->count(),
            'processing_complaints' => Complaint::where('status', 'processing')->count(),
            'completed_complaints' => Complaint::where('status', 'completed')->count(),
        ];

        $recentComplaints = Complaint::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('supervision.complaints', compact('stats', 'recentComplaints'));
    }

    /**
     * Submit complaint form
     */
    public function submitComplaintForm()
    {
        return view('supervision.complaint-form');
    }

    /**
     * Submit complaint
     */
    public function submitComplaint(Request $request)
    {
        $validated = $request->validate([
            'complaint_type' => 'required|in:complaint,suggestion,whistleblowing',
            'reporter_name' => 'required|string|max:255',
            'reporter_email' => 'required|email|max:255',
            'reporter_phone' => 'nullable|string|max:20',
            'complaint_title' => 'required|string|max:255',
            'complaint_description' => 'required|string|max:2000',
            'incident_date' => 'nullable|date|before_or_equal:today',
            'attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf',
        ]);

        $complaint = Complaint::create([
            'complaint_number' => $this->generateComplaintNumber(),
            'complaint_type' => $validated['complaint_type'],
            'reporter_name' => $validated['reporter_name'],
            'reporter_email' => $validated['reporter_email'],
            'reporter_phone' => $validated['reporter_phone'] ?? null,
            'complainant_name' => $validated['reporter_name'],
            'complainant_email' => $validated['reporter_email'],
            'complainant_contact' => $validated['reporter_phone'] ?? null,
            'title' => $validated['complaint_title'],
            'subject' => $validated['complaint_title'],
            'description' => $validated['complaint_description'],
            'incident_date' => $validated['incident_date'] ?? null,
            'status' => 'submitted',
            'anonymous' => false,
        ]);

        $this->storeEvidenceFile($request, $complaint);

        return redirect()->route('supervision.complaint.success', $complaint->complaint_number)
            ->with('success', 'Pengaduan berhasil disimpan dengan nomor: ' . $complaint->complaint_number);
    }

    /**
     * Store the (optional) single evidence attachment as a JSON-encoded
     * array of storage paths on the complaint's evidence_files column.
     */
    private function storeEvidenceFile(Request $request, Complaint $complaint): void
    {
        if (!$request->hasFile('attachment')) {
            return;
        }

        $files = $request->file('attachment');
        $files = is_array($files) ? $files : [$files];

        $paths = [];
        foreach ($files as $file) {
            // Stored privately — evidence (especially whistleblowing) must not
            // be reachable via a guessable public URL. Served only through
            // Admin\ComplaintController::downloadEvidence() to admin staff.
            $paths[] = $file->store('complaint-evidence', 'local');
        }

        $complaint->update(['evidence_files' => json_encode($paths)]);
    }

    /**
     * Complaint success
     */
    public function complaintSuccess($complaintNumber)
    {
        $complaint = Complaint::where('complaint_number', $complaintNumber)->firstOrFail();
        return view('supervision.complaint-success', compact('complaint'));
    }

    /**
     * Show complaint tracking form
     */
    public function showTrackForm()
    {
        return view('supervision.complaint-track-form');
    }

    /**
     * Track complaint
     */
    public function trackComplaint(Request $request)
    {
        $validated = $request->validate([
            'complaint_number' => 'required|string',
            'reporter_email' => 'required|email',
        ]);

        // Ownership must always be verified by the reporter's own email —
        // never look up a complaint (especially whistleblowing) by number alone.
        $complaint = Complaint::where('complaint_number', $validated['complaint_number'])
            ->where(function ($q) use ($validated) {
                $q->where('reporter_email', $validated['reporter_email'])
                  ->orWhere('complainant_email', $validated['reporter_email']);
            })
            ->firstOrFail();

        return view('supervision.complaint-track-result', compact('complaint'));
    }

    /**
     * Whistleblowing form
     */
    public function whistleblowingForm()
    {
        return view('supervision.complaint-form', ['activeTab' => 'whistleblowing']);
    }

    /**
     * Submit whistleblowing
     */
    public function submitWhistleblowing(Request $request)
    {
        $validated = $request->validate([
            'violation_category' => 'nullable|string|max:255',
            'complaint_title' => 'required|string|max:255',
            'complaint_description' => 'required|string|max:2000',
            'incident_date' => 'nullable|date',
            'anonymous' => 'nullable|boolean',
            'reporter_name' => 'nullable|string|max:255',
            'reporter_email' => 'nullable|email|max:255',
            'reporter_phone' => 'nullable|string|max:20',
            'attachment.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf',
        ]);

        $isAnonymous = (bool) ($validated['anonymous'] ?? false);

        $complaint = Complaint::create([
            'complaint_number' => $this->generateComplaintNumber(),
            'complaint_type' => 'whistleblowing',
            'category' => $validated['violation_category'] ?? null,
            'title' => $validated['complaint_title'],
            'subject' => $validated['complaint_title'],
            'description' => $validated['complaint_description'],
            'incident_date' => $validated['incident_date'] ?? null,
            'reporter_name' => $isAnonymous ? 'Anonim' : ($validated['reporter_name'] ?? 'Anonim'),
            'reporter_email' => $isAnonymous ? null : ($validated['reporter_email'] ?? null),
            'reporter_phone' => $isAnonymous ? null : ($validated['reporter_phone'] ?? null),
            'status' => 'submitted',
            'anonymous' => $isAnonymous,
            'is_whistleblowing' => true,
            'is_confidential' => true,
        ]);

        $this->storeEvidenceFile($request, $complaint);

        return redirect()->route('supervision.whistleblowing.success', $complaint->complaint_number)
            ->with('success', 'Laporan whistleblowing berhasil disimpan dengan nomor: ' . $complaint->complaint_number);
    }

    /**
     * Whistleblowing success
     */
    public function whistleblowingSuccess($complaintNumber)
    {
        $complaint = Complaint::where('complaint_number', $complaintNumber)->firstOrFail();
        return view('supervision.whistleblowing-success', compact('complaint'));
    }

    /**
     * Generate complaint number
     */
    private function generateComplaintNumber()
    {
        $prefix = match(request()->segment(2)) {
            'complaints' => 'KPL',
            'whistleblowing' => 'WBL',
            default => 'KPL',
        };

        $date = Carbon::now()->format('Ym');

        // Count alone can collide with a soft-deleted row still holding a
        // number (unique constraint applies regardless of deleted_at), so
        // derive the next sequence from the highest existing number instead.
        $lastNumber = Complaint::withTrashed()
            ->where('complaint_number', 'like', "{$prefix}-{$date}-%")
            ->orderByRaw("CAST(RIGHT(complaint_number, 4) AS INTEGER) DESC")
            ->value('complaint_number');

        $sequence = $lastNumber ? ((int) substr($lastNumber, -4)) + 1 : 1;

        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }
}