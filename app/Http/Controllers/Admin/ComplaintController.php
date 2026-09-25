<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    /** Complaint & suggestion reports (Dumas). */
    public function index(Request $request)
    {
        return $this->listing($request, false);
    }

    /** Whistleblowing reports (WBS), kept apart from the general list. */
    public function whistleblowingIndex(Request $request)
    {
        return $this->listing($request, true);
    }

    public function create()
    {
        $services = Service::orderBy('name')->get(['id', 'name']);

        return view('admin.complaints.create', compact('services'));
    }

    /** Record a complaint received offline (letter, phone, in person). */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'complaint_type' => 'required|in:complaint,suggestion',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'reporter_name' => 'nullable|string|max:255',
            'reporter_email' => 'nullable|email|max:255',
            'reporter_phone' => 'nullable|string|max:30',
            'service_id' => 'nullable|exists:services,id',
            'priority' => 'required|in:' . implode(',', array_keys(Complaint::PRIORITIES)),
        ]);

        $complaint = Complaint::create($validated + [
            'complaint_number' => $this->nextNumber('KPL'),
            'subject' => $validated['title'],
            'complainant_name' => $validated['reporter_name'] ?? null,
            'complainant_email' => $validated['reporter_email'] ?? null,
            'complainant_contact' => $validated['reporter_phone'] ?? null,
            'status' => 'submitted',
        ]);

        return redirect()->route('admin.complaints.show', $complaint)->with('success', 'Pengaduan berhasil dicatat.');
    }

    public function show(Complaint $complaint)
    {
        if ($complaint->complaint_type === 'whistleblowing') {
            return redirect()->route('admin.whistleblowing.show', $complaint);
        }

        return $this->detail($complaint);
    }

    public function whistleblowingShow(Complaint $complaint)
    {
        abort_unless($complaint->complaint_type === 'whistleblowing', 404);

        return $this->detail($complaint);
    }

    /** The follow-up form lives on the detail page. */
    public function edit(Complaint $complaint)
    {
        return redirect()->route('admin.complaints.show', $complaint);
    }

    /** Follow-up (Modul 10): stage, handler, priority and the reply to the reporter. */
    public function update(Request $request, Complaint $complaint)
    {
        $this->authorize('update', $complaint);

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Complaint::STATUSES)),
            'priority' => 'required|in:' . implode(',', array_keys(Complaint::PRIORITIES)),
            'assigned_to' => 'nullable|exists:users,id',
            'response' => 'nullable|string|max:5000',
            'resolution_notes' => 'nullable|string|max:5000',
        ]);

        $finished = in_array($validated['status'], ['resolved', 'closed'], true);

        if ($finished && blank($validated['response'] ?? null) && blank($complaint->response)) {
            return back()->withInput()->withErrors(['response' => 'Isi tanggapan untuk pelapor sebelum menyelesaikan laporan.']);
        }

        $complaint->update($validated + [
            'assigned_to_id' => $validated['assigned_to'] ?? null,
            'resolved_at' => $finished ? ($complaint->resolved_at ?? now()) : null,
            'resolved_by' => $finished ? ($complaint->resolved_by ?? $request->user()->id) : null,
        ]);

        $route = $complaint->complaint_type === 'whistleblowing' ? 'admin.whistleblowing.show' : 'admin.complaints.show';

        return redirect()->route($route, $complaint)->with('success', 'Tindak lanjut berhasil disimpan.');
    }

    /** Kept for existing forms; same follow-up as update(). */
    public function updateWhistleblowingStatus(Request $request, Complaint $complaint)
    {
        abort_unless($complaint->complaint_type === 'whistleblowing', 404);

        return $this->update($request, $complaint);
    }

    public function destroy(Complaint $complaint)
    {
        $wasWhistleblowing = $complaint->complaint_type === 'whistleblowing';
        $complaint->delete();

        return redirect()->route($wasWhistleblowing ? 'admin.whistleblowing.index' : 'admin.complaints.index')
            ->with('success', 'Laporan berhasil dihapus.');
    }

    /**
     * Download an evidence file attached to a complaint/whistleblowing report.
     * Files are stored on the private disk and served only to complaint
     * handlers (ComplaintPolicy::view).
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

    private function listing(Request $request, bool $whistleblowing)
    {
        $this->authorize('viewAny', Complaint::class);

        $base = Complaint::query()->when(
            $whistleblowing,
            fn ($q) => $q->where('complaint_type', 'whistleblowing'),
            fn ($q) => $q->whereIn('complaint_type', ['complaint', 'suggestion']),
        );

        $counts = (clone $base)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $complaints = $base
            ->with(['service:id,name', 'assignee:id,name'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when(! $whistleblowing && $request->filled('type'), fn ($q) => $q->where('complaint_type', $request->type))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%' . $request->search . '%';
                $q->where(fn ($q) => $q->where('title', 'ilike', $term)
                    ->orWhere('complaint_number', 'ilike', $term)
                    ->orWhere('description', 'ilike', $term)
                    ->orWhere('reporter_name', 'ilike', $term));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.complaints.index', compact('complaints', 'counts', 'whistleblowing'));
    }

    private function detail(Complaint $complaint)
    {
        $this->authorize('view', $complaint);

        $complaint->load(['service:id,name', 'assignee:id,name', 'resolver:id,name', 'user:id,name']);

        $handlers = User::role(\App\Support\RoleAccess::COMPLAINT_HANDLERS)->orderBy('name')->get(['id', 'name']);

        return view('admin.complaints.show', compact('complaint', 'handlers'));
    }

    private function nextNumber(string $prefix): string
    {
        $date = now()->format('Ym');

        $last = Complaint::withTrashed()
            ->where('complaint_number', 'like', "{$prefix}-{$date}-%")
            ->orderByRaw('CAST(RIGHT(complaint_number, 4) AS INTEGER) DESC')
            ->value('complaint_number');

        return sprintf('%s-%s-%04d', $prefix, $date, $last ? ((int) substr($last, -4)) + 1 : 1);
    }
}
