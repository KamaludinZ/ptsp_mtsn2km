<?php

namespace App\Http\Controllers\Api;

use App\Enums\SignatureModel;
use App\Exceptions\TicketActionException;
use App\Http\Controllers\Controller;
use App\Models\DispositionLog;
use App\Models\Ticket;
use App\Services\TicketService;
use App\Support\DispositionHistory;
use App\Support\ServiceDisposition;
use App\Support\TicketLabels;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Proses disposisi for leaders: the incoming queue and the decision. */
class DispositionController extends Controller
{
    public function __construct(private TicketService $tickets)
    {
    }

    /** GET /api/disposisi/antrean: requests waiting for this leader's disposition, oldest first. */
    public function queue(Request $request): JsonResponse
    {
        $tickets = Ticket::approvableBy($request->user());

        $active = \App\Support\ActiveRoles::inContext($request->user());

        return response()->json([
            // The queue follows the active role; an empty queue may just mean another role is active.
            'peran_aktif' => $active,
            'memutuskan_sebagai_pimpinan' => \App\Support\ActiveRoles::actsAsLeader($request->user()),
            'total' => $tickets->count(),
            'data' => $tickets->map(fn (Ticket $ticket) => [
                'nomor_tiket' => $ticket->ticket_number,
                'layanan' => $ticket->service?->name,
                'pemohon' => $ticket->user?->name,
                'status' => $ticket->status,
                'status_label' => TicketLabels::status($ticket->status),
                'diajukan' => $ticket->created_at?->toIso8601String(),
                'target_selesai' => $ticket->estimated_completion_date?->toDateString(),
                'terlambat' => $ticket->isOverdue(),
                'anjuran_tanda_tangan' => $ticket->service?->signature_recommendation,
                'penerima_bawaan' => $ticket->service?->disposition_roles ?? [],
                'penerima_bawaan_label' => ServiceDisposition::recipients($ticket->service?->disposition_roles),
            ])->values(),
        ]);
    }

    /**
     * POST /api/disposisi/{ticket:ticket_number}
     * keputusan=disposisi: model_tanda_tangan, penerima[], instruksi, catatan
     * keputusan=tolak: catatan (alasan, wajib)
     */
    public function decide(Request $request, Ticket $ticket): JsonResponse
    {
        // 403 with the reason, e.g. which role to make active.
        \Illuminate\Support\Facades\Gate::forUser($request->user())->authorize('approve', $ticket);

        $data = $request->validate([
            'keputusan' => ['required', Rule::in(['disposisi', 'tolak'])],
            'model_tanda_tangan' => ['required_if:keputusan,disposisi', 'nullable', Rule::enum(SignatureModel::class)],
            'penerima' => ['nullable', 'array'],
            'penerima.*' => [Rule::in(array_keys(ServiceDisposition::RECIPIENTS))],
            'instruksi' => ['nullable', 'string', 'max:255'],
            'didisposisi_oleh' => ['required_if:model_tanda_tangan,acknowledged_by', 'nullable', 'string', 'max:255'],
            'catatan' => ['required_if:keputusan,tolak', 'nullable', 'string', 'max:500'],
        ]);

        try {
            $data['keputusan'] === 'disposisi'
                ? $this->tickets->dispose($ticket, $request->user(), $data['model_tanda_tangan'], $data['penerima'] ?? [], $data['instruksi'] ?? null, $data['catatan'] ?? null, acknowledgedBy: $data['didisposisi_oleh'] ?? null)
                : $this->tickets->decide($ticket, false, $request->user(), null, $data['catatan']);
        } catch (TicketActionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $ticket->refresh();

        return response()->json([
            'nomor_tiket' => $ticket->ticket_number,
            'status' => $ticket->status,
            'status_label' => TicketLabels::status($ticket->status),
            'disposisi' => collect(DispositionHistory::forTicket($ticket))->last(),
        ]);
    }

    /** POST /api/disposisi/riwayat/{disposition}/tanda-tangan (multipart: berkas) */
    public function uploadSignature(Request $request, DispositionLog $disposition): JsonResponse
    {
        $this->authorize('uploadSignature', $disposition);

        $request->validate([
            'berkas' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $upload = $request->file('berkas');
        $path = $upload->store('ticket-files', 'local');
        $file = $this->tickets->attachSignedSheet($disposition, $path, $upload->getClientOriginalName(), $request->user());

        $entry = collect(DispositionHistory::forTicket($disposition->ticket))->firstWhere('id', $disposition->id);

        return response()->json([
            'berkas' => $file->file_name,
            'disposisi' => $entry,
        ], 201);
    }
}
