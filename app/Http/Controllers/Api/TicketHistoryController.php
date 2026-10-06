<?php

namespace App\Http\Controllers\Api;

use App\Exports\ServiceHistoryExport;
use App\Filament\Resources\ServiceHistoryResource;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketLog;
use App\Support\DispositionHistory;
use App\Support\DocumentHistory;
use App\Support\TicketLabels;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Riwayat layanan of one ticket, oldest first. The applicant sees what the
 * public tracking page shows; staff also see actors, documents and details;
 * IP addresses are for auditors (admin, supervision) only.
 */
class TicketHistoryController extends Controller
{
    /** GET /api/tiket/{ticket:ticket_number}/riwayat */
    public function show(Request $request, Ticket $ticket): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->can('view', $ticket), 403);

        $staff = $user->isStaff();
        $auditor = $user->hasRole('admin') || $user->can('supervision.access');

        $logs = $ticket->logs()->with(['performer:id,name', 'file'])->oldest()->oldest('id')->get();

        return response()->json([
            'nomor_tiket' => $ticket->ticket_number,
            'status' => $ticket->status,
            'status_label' => TicketLabels::status($ticket->status),
            'anjuran_tanda_tangan' => $staff ? $ticket->service?->signature_recommendation : null,
            'riwayat' => $logs->map(fn (TicketLog $log) => array_filter([
                'waktu' => $log->created_at?->toIso8601String(),
                'kegiatan' => $log->action,
                'kegiatan_label' => TicketLabels::logAction($log->action),
                'status_awal' => $log->from_status,
                'status_akhir' => $log->to_status,
                'perubahan_status' => $log->statusChange(),
                'catatan' => $log->notes,
                'pelaku' => $staff ? ($log->performer?->name ?? 'Sistem') : null,
                'sebagai' => $staff && $log->acting_role ? \App\Support\RoleAccess::roleLabel($log->acting_role) : null,
                'berkas' => $staff ? (collect($log->relatedDocuments())->map(fn ($url, $name) => ['nama' => $name, 'url' => $url])->values()->all() ?: null) : null,
                'detail' => $staff ? $log->metadata : null,
                'ip' => $auditor ? $log->ip_address : null,
            ], fn ($value) => $value !== null))->values(),
        ]);
    }

    /** GET /api/tiket/{ticket:ticket_number}/disposisi (staff) */
    public function dispositions(Request $request, Ticket $ticket): JsonResponse
    {
        abort_unless($request->user()->isStaff() && $request->user()->can('view', $ticket), 403);

        return response()->json([
            'nomor_tiket' => $ticket->ticket_number,
            'anjuran_tanda_tangan' => $ticket->service?->signature_recommendation,
            'disposisi' => collect(DispositionHistory::forTicket($ticket))->map(fn (array $entry) => [
                'waktu' => $entry['at']?->toIso8601String(),
                'pejabat' => $entry['actor'],
                'jabatan' => $entry['role'],
                'keputusan' => $entry['action'],
                'keputusan_label' => DispositionHistory::action($entry['action']),
                'model_tanda_tangan' => $entry['signature_model'],
                'model_tanda_tangan_label' => $entry['signature_model'] ? DispositionHistory::signatureModel($entry['signature_model']) : null,
                'berkas_tanda_tangan' => $entry['signature_file'],
                'didisposisi_oleh' => $entry['acknowledged_by'],
                'penerima' => $entry['recipients'],
                'instruksi' => $entry['instruction'],
                'catatan' => $entry['note'],
            ])->values(),
        ]);
    }

    /** GET /api/tiket/{ticket:ticket_number}/berkas (staff): uploads and service-output versions. */
    public function documents(Request $request, Ticket $ticket): JsonResponse
    {
        abort_unless($request->user()->isStaff() && $request->user()->can('view', $ticket), 403);

        return response()->json([
            'nomor_tiket' => $ticket->ticket_number,
            'berkas' => collect(DocumentHistory::forTicket($ticket))->map(fn (array $entry) => [
                'waktu' => $entry['log']->created_at?->toIso8601String(),
                'jenis' => $entry['type'],
                'keterangan' => $entry['log']->notes,
                'oleh' => $entry['log']->performer?->name ?? 'Sistem',
                'unduh' => collect($entry['documents'])->map(fn ($url, $name) => ['nama' => $name, 'url' => $url])->values(),
                'diganti' => $entry['replaced'],
            ])->values(),
        ]);
    }

    /**
     * GET /api/riwayat: every ticket's history, searchable and filterable
     * (q, layanan, pelaku, kegiatan, status, dari, sampai), newest first.
     */
    public function index(Request $request): JsonResponse
    {
        abort_unless(ServiceHistoryResource::canViewAny(), 403);

        $filters = $this->filters($request);

        $page = $this->historyQuery($filters)
            ->with(['ticket:id,ticket_number,service_id', 'ticket.service:id,name', 'performer:id,name'])
            ->paginate($filters['per_halaman'] ?? 25)
            ->withQueryString();

        return response()->json([
            'data' => collect($page->items())->map(fn (TicketLog $log) => [
                'waktu' => $log->created_at?->toIso8601String(),
                'nomor_tiket' => $log->ticket?->ticket_number,
                'layanan' => $log->ticket?->service?->name,
                'kegiatan' => $log->action,
                'kegiatan_label' => TicketLabels::logAction($log->action),
                'perubahan_status' => $log->statusChange(),
                'pelaku' => $log->performer?->name ?? 'Sistem',
                'sebagai' => $log->acting_role ? \App\Support\RoleAccess::roleLabel($log->acting_role) : null,
                'catatan' => $log->notes,
            ]),
            'halaman' => $page->currentPage(),
            'total' => $page->total(),
            'halaman_terakhir' => $page->lastPage(),
        ]);
    }

    /** GET /api/riwayat/ekspor: the filtered history as an Excel file for auditors. */
    public function export(Request $request)
    {
        abort_unless(ServiceHistoryResource::canViewAny(), 403);

        $filters = $this->filters($request);
        $query = $this->historyQuery($filters);

        activity('audit')
            ->causedBy($request->user())
            ->withProperties(['rows' => (clone $query)->count(), 'filters' => $filters, 'via' => 'api'])
            ->log('Mengekspor riwayat layanan');

        return Excel::download(new ServiceHistoryExport($query), 'riwayat-layanan-' . now()->format('Ymd-His') . '.xlsx');
    }

    private function filters(Request $request): array
    {
        return $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'layanan' => ['nullable', 'integer'],
            'pelaku' => ['nullable', 'integer'],
            'kegiatan' => ['nullable', 'in:' . implode(',', array_keys(TicketLabels::LOG_ACTIONS)) . ',status_changed'],
            'status' => ['nullable', 'in:' . implode(',', array_keys(TicketLabels::STATUSES))],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            'per_halaman' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
    }

    private function historyQuery(array $filters): Builder
    {
        return TicketLog::query()
            ->when($filters['q'] ?? null, function ($query, string $term) {
                $like = '%' . addcslashes($term, '%_\\') . '%';
                $query->where(fn ($q) => $q->where('notes', 'ilike', $like)
                    ->orWhereHas('ticket', fn ($t) => $t->where('ticket_number', 'ilike', $like))
                    ->orWhereHas('performer', fn ($u) => $u->where('name', 'ilike', $like)));
            })
            ->when($filters['layanan'] ?? null, fn ($q, $service) => $q->whereHas('ticket', fn ($t) => $t->where('service_id', $service)))
            ->when($filters['pelaku'] ?? null, fn ($q, $user) => $q->where('performed_by', $user))
            ->when($filters['kegiatan'] ?? null, fn ($q, $action) => $q->where('action', $action))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('to_status', $status))
            ->when($filters['dari'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['sampai'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->latest()->latest('id');
    }
}
