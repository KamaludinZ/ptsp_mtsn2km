<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketLog;
use App\Support\ServiceMetrics;
use Illuminate\Http\Request;

/**
 * Leadership area (Kepala Sekolah, Kepala TU): executive dashboard (Modul 13)
 * and service approvals (Modul 8).
 */
class LeadershipController extends Controller
{
    public function dashboard(Request $request)
    {
        return view('leadership.dashboard', ServiceMetrics::overview(ServiceMetrics::period($request->query('periode'))) + [
            'myApprovals' => $this->approvable()->count(),
        ]);
    }

    public function approvals()
    {
        $pending = $this->approvable();

        $history = Ticket::with(['service:id,name', 'user:id,name'])
            ->where('approved_by', auth()->id())
            ->whereNotNull('approved_at')
            ->latest('approved_at')
            ->limit(15)
            ->get();

        return view('leadership.approvals', compact('pending', 'history'));
    }

    public function decide(Request $request, Ticket $ticket)
    {
        $this->authorize('approve', $ticket);

        abort_unless(Ticket::whereKey($ticket->id)->awaitingApproval()->exists(), 409, 'Tiket ini tidak sedang menunggu persetujuan.');

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'signature_type' => 'required_if:action,approve|nullable|in:tte,ttd',
            'notes' => 'required_if:action,reject|nullable|string|max:500',
        ], [
            'signature_type.required_if' => 'Pilih jenis tanda tangan (TTE atau TTD).',
            'notes.required_if' => 'Tuliskan alasan penolakan.',
        ]);

        $approved = $validated['action'] === 'approve';
        $fromStatus = $ticket->status;

        $ticket->update([
            'approval_status' => $approved ? 'approved' : 'rejected',
            'is_approved' => $approved,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_notes' => $validated['notes'] ?? null,
            'signature_type' => $approved ? $validated['signature_type'] : null,
            'status' => $approved ? 'approved' : 'rejected',
        ]);

        TicketLog::create([
            'ticket_id' => $ticket->id,
            'action' => $approved ? 'approved' : 'rejected',
            'performed_by' => auth()->id(),
            'from_status' => $fromStatus,
            'to_status' => $ticket->status,
            'notes' => $approved
                ? trim('Disetujui pimpinan ('.strtoupper($validated['signature_type']).'). '.($validated['notes'] ?? ''))
                : 'Ditolak pimpinan: '.$validated['notes'],
        ]);

        return redirect()->route('leadership.approvals')->with('success', $approved
            ? "Tiket {$ticket->ticket_number} disetujui. Petugas TU dapat menyiapkan produk layanan."
            : "Tiket {$ticket->ticket_number} ditolak.");
    }

    private function approvable()
    {
        return Ticket::approvableBy(auth()->user());
    }
}
