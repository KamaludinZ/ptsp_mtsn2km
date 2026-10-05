<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\TicketFile;
use App\Models\TicketLog;
use App\Services\TicketService;
use App\Support\TicketDocuments;
use App\Support\TicketLabels;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Portal pemohon: an applicant's own requests. */
class ApplicantTicketController extends Controller
{
    /** GET /api/permohonan?status=&per_halaman= */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:' . implode(',', array_keys(TicketLabels::STATUSES))],
            'per_halaman' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $page = Ticket::query()
            ->where('user_id', $request->user()->id)
            ->with('service:id,name')
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate($filters['per_halaman'] ?? 10)
            ->withQueryString();

        return response()->json([
            'data' => collect($page->items())->map(fn (Ticket $ticket) => [
                'nomor_tiket' => $ticket->ticket_number,
                'layanan' => $ticket->service?->name,
                'status' => $ticket->status,
                'status_label' => TicketLabels::status($ticket->status),
                'diajukan' => $ticket->created_at?->toIso8601String(),
                'target_selesai' => $ticket->estimated_completion_date?->toDateString(),
                'hasil_tersedia' => $ticket->status === 'completed',
            ]),
            'halaman' => $page->currentPage(),
            'total' => $page->total(),
            'halaman_terakhir' => $page->lastPage(),
        ]);
    }

    /** GET /api/permohonan/{ticket:ticket_number} */
    public function show(Request $request, Ticket $ticket): JsonResponse
    {
        abort_unless($ticket->user_id === $request->user()->id, 404);

        // Applicants never see internal notes; only milestones (as in the portal).
        $ticket->load(['service', 'files', 'output', 'logs' => fn ($q) => $q->whereIn('action', TicketLog::APPLICANT_VISIBLE)]);

        return response()->json([
            'nomor_tiket' => $ticket->ticket_number,
            'layanan' => $ticket->service?->name,
            'status' => $ticket->status,
            'status_label' => TicketLabels::status($ticket->status),
            'mode' => $ticket->mode,
            'keterangan' => $ticket->notes,
            'diajukan' => $ticket->created_at?->toIso8601String(),
            'target_selesai' => $ticket->estimated_completion_date?->toDateString(),
            'selesai' => $ticket->actual_completion_date?->toDateString(),
            'siap_diambil' => (bool) $ticket->ready_for_pickup,
            'berkas' => $ticket->files->map(fn (TicketFile $file) => [
                'nama' => $file->file_name,
                'unduh' => route('documents.ticket-file', $file),
            ])->values(),
            'hasil' => $ticket->output?->file_path ? route('documents.ticket-output', $ticket->output) : null,
            'tanda_terima' => route('tickets.receipt', $ticket),
            'riwayat' => $ticket->logs->sortBy('created_at')->values()->map(fn (TicketLog $log) => [
                'waktu' => $log->created_at?->toIso8601String(),
                'kegiatan' => TicketLabels::logAction($log->action),
                'perubahan_status' => $log->statusChange(),
                'catatan' => $log->notes,
            ]),
        ]);
    }

    /** POST /api/permohonan (multipart): layanan, keterangan, prioritas, berkas[] */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if($user->isStaff(), 403, 'Petugas mendaftarkan permohonan melalui loket.');

        $data = $request->validate([
            'layanan' => ['required', 'string'],
            'keterangan' => ['required', 'string', 'max:1000'],
            'prioritas' => ['nullable', 'in:normal,high,urgent'],
            'berkas' => ['nullable', 'array', 'max:10'],
            'berkas.*' => ['file', 'max:10240', 'mimes:' . TicketDocuments::MIMES],
        ]);

        $service = Service::query()
            ->where('is_active', true)
            ->where('slug', $data['layanan'])
            ->availableFor($user->user_type)
            ->first();

        if (! $service) {
            return response()->json(['message' => 'Layanan tidak tersedia untuk kategori akun Anda.', 'errors' => ['layanan' => ['Layanan tidak tersedia.']]], 422);
        }

        $files = collect($request->file('berkas', []))
            ->mapWithKeys(fn ($upload) => [$upload->store('ticket-files', 'local') => $upload->getClientOriginalName()])
            ->all();

        $ticket = app(TicketService::class)->open($service, $user, $user, 'online', $data['keterangan'], $data['prioritas'] ?? 'normal', $files);

        return response()->json([
            'nomor_tiket' => $ticket->ticket_number,
            'status' => $ticket->status,
            'target_selesai' => $ticket->estimated_completion_date?->toDateString(),
            'detail' => route('api.permohonan.show', $ticket),
            'tanda_terima' => route('tickets.receipt', $ticket),
        ], 201);
    }

    /** POST /api/permohonan/{ticket:ticket_number}/berkas (multipart: berkas[]): add missing requirements. */
    public function upload(Request $request, Ticket $ticket): JsonResponse
    {
        abort_unless($ticket->user_id === $request->user()->id, 404);

        $request->validate([
            'berkas' => ['required', 'array', 'min:1', 'max:10'],
            'berkas.*' => ['file', 'max:10240', 'mimes:' . TicketDocuments::MIMES],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        if (! in_array($ticket->status, Ticket::OPEN_STATUSES, true)) {
            return response()->json(['message' => 'Berkas hanya dapat ditambahkan selama permohonan masih diproses.'], 422);
        }

        $added = collect($request->file('berkas'))->map(function ($upload) use ($ticket, $request) {
            $path = $upload->store('ticket-files', 'local');
            app(TicketService::class)->attachFile($ticket, $path, $upload->getClientOriginalName(), $request->user(), $request->input('keterangan') ?: 'Berkas susulan dari pemohon.');

            return $upload->getClientOriginalName();
        });

        return response()->json(['ditambahkan' => $added->values()], 201);
    }
}
