<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Services\ComplaintService;
use Illuminate\Http\Request;

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

        $tab = old('form', request('tab'));
        $activeTab = in_array($tab, ['whistleblowing', 'saran'], true) ? $tab : 'dumas';

        return view('supervision.complaints', compact('stats', 'recentComplaints', 'activeTab'));
    }

    /**
     * Submit complaint
     */
    public function submitComplaint(Request $request)
    {
        // This page sends complaints; suggestions and whistleblowing have their own forms.
        $validated = $request->validate(ComplaintService::rulesFor('complaint'));

        $complaint = app(ComplaintService::class)->submit('complaint', $validated, array_filter([$request->file('attachment')]));

        return redirect()->route('supervision.complaint.success', $complaint->complaint_number)
            ->with('success', 'Pengaduan berhasil disimpan dengan nomor: ' . $complaint->complaint_number);
    }

    /**
     * Submit a suggestion (saran): only the text is required.
     */
    public function submitSuggestion(Request $request)
    {
        $validated = $request->validate(ComplaintService::rulesFor('suggestion'), [
            'suggestion.required' => 'Tuliskan saran Anda.',
        ]);

        app(ComplaintService::class)->submit('suggestion', $validated);

        return redirect()->route('supervision.complaints.dashboard', ['tab' => 'saran'])
            ->with('suggestion_success', 'Terima kasih! Saran Anda sudah kami terima.');
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
     * Submit whistleblowing
     */
    public function submitWhistleblowing(Request $request)
    {
        $validated = $request->validate(ComplaintService::rulesFor('whistleblowing'));

        $complaint = app(ComplaintService::class)->submit('whistleblowing', $validated, array_filter([$request->file('attachment')]));

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
}
