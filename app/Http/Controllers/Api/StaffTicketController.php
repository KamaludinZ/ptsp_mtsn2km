<?php

namespace App\Http\Controllers\Api;

use App\Filament\Resources\TicketResource;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Support\IncomingCategory;
use App\Support\ServiceDisposition;
use App\Support\ServiceMetrics;
use App\Support\StatusBadge;
use App\Support\TicketLabels;
use App\Support\TicketTabs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Kelola Permohonan for staff: the request list with the panel's tabs,
 * keyword search and filters.
 */
class StaffTicketController extends Controller
{
    public const SORTS = ['terbaru', 'terlama', 'target'];

    /**
     * GET /api/tiket?tab=&q=&status[]=&layanan[]=&periode=|dari=&sampai=&kategori=&unit=&petugas=&jalur=&prioritas=&terlambat=&urut=&per_halaman=
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->can('viewAny', Ticket::class), 403);

        $filters = $request->validate([
            'tab' => ['nullable', Rule::in(TicketTabs::for($user))],
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'array'],
            'status.*' => [Rule::in(array_keys(ServiceMetrics::STATUS_LABELS))],
            'layanan' => ['nullable', 'array'],
            'layanan.*' => ['integer'],
            'periode' => ['nullable', Rule::in(array_keys(TicketResource::DATE_PERIODS))],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            'kategori' => ['nullable', Rule::in(array_keys(IncomingCategory::CATEGORIES))],
            'unit' => ['nullable', Rule::in(array_keys(ServiceDisposition::RECIPIENTS))],
            'petugas' => ['nullable', 'integer'],
            'jalur' => ['nullable', Rule::in(array_keys(TicketLabels::MODES))],
            'prioritas' => ['nullable', Rule::in(array_keys(TicketLabels::PRIORITIES))],
            'terlambat' => ['nullable', 'boolean'],
            'urut' => ['nullable', Rule::in(self::SORTS)],
            'per_halaman' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $tab = $filters['tab'] ?? TicketTabs::default($user);

        $narrow = fn (Builder $query) => $this->filter($query, $filters);
        $query = Ticket::query()->with(['user:id,name,whatsapp_number', 'service:id,name', 'assignedTo:id,name', 'latestStatusHistory']);
        $narrow($query);
        TicketTabs::apply($query, $tab, $user);

        match ($filters['urut'] ?? ($tab === 'antrian' ? 'target' : 'terbaru')) {
            'terlama' => $query->oldest()->oldest('id'),
            'target' => $query->orderByRaw('estimated_completion_date asc nulls last')->oldest(),
            default => $query->latest()->latest('id'),
        };

        $page = $query->paginate($filters['per_halaman'] ?? 20)->withQueryString();

        return response()->json([
            'data' => collect($page->items())->map(fn (Ticket $ticket) => self::item($ticket)),
            'tab' => $tab,
            'tab_tersedia' => collect(TicketTabs::counts($user, $narrow))
                ->map(fn (int $count, string $key) => ['kunci' => $key, 'label' => TicketTabs::LABELS[$key], 'jumlah' => $count])
                ->values(),
            'meta' => [
                'halaman' => $page->currentPage(),
                'per_halaman' => $page->perPage(),
                'total' => $page->total(),
                'halaman_terakhir' => $page->lastPage(),
            ],
        ]);
    }

    /** GET /api/tiket/pilihan-filter: values the list's search and filters accept, with labels. */
    public function filterOptions(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->can('viewAny', Ticket::class), 403);

        $pairs = fn (array $options) => collect($options)->map(fn ($label, $value) => ['nilai' => $value, 'label' => $label])->values();

        return response()->json([
            'tab' => collect(TicketTabs::for($user))->map(fn (string $tab) => ['nilai' => $tab, 'label' => TicketTabs::LABELS[$tab]]),
            'tab_bawaan' => TicketTabs::default($user),
            'status' => $pairs(ServiceMetrics::STATUS_LABELS),
            'layanan' => \App\Models\Service::orderBy('name')->get(['id', 'name'])->map(fn ($service) => ['nilai' => $service->id, 'label' => $service->name]),
            'periode' => $pairs(TicketResource::DATE_PERIODS),
            'kategori' => $pairs(IncomingCategory::CATEGORIES),
            'unit' => $pairs(ServiceDisposition::RECIPIENTS),
            'petugas' => \App\Models\User::role(\App\Models\User::STAFF_ROLES)->where('is_active', true)->orderBy('name')->get(['id', 'name'])
                ->map(fn ($officer) => ['nilai' => $officer->id, 'label' => $officer->name]),
            'jalur' => $pairs(TicketLabels::MODES),
            'prioritas' => $pairs(TicketLabels::PRIORITIES),
            'urut' => $pairs(['terbaru' => 'Terbaru', 'terlama' => 'Terlama', 'target' => 'Target selesai terdekat']),
            'pencarian' => [
                'cakupan' => 'Nomor tiket, nama/email/WhatsApp pemohon, layanan, petugas, dan keterangan permohonan.',
                'minimal_huruf' => \App\Support\TicketSearch::MIN_LENGTH,
            ],
        ]);
    }

    /** GET /api/tiket/{ticket:ticket_number}: everything the detail page shows. */
    public function show(Request $request, Ticket $ticket): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->isStaff() && $user->can('view', $ticket), 403);

        $ticket->load(['user', 'service', 'assignedTo:id,name', 'approver:id,name', 'files', 'output', 'workflowSteps.workflowStep', 'latestStatusHistory', 'statusHistories.changer:id,name']);
        $services = app(\App\Services\TicketService::class);
        $email = $ticket->user?->email;

        return response()->json(self::item($ticket) + [
            'keterangan' => $ticket->notes,
            'pemohon' => $ticket->user ? [
                'nama' => $ticket->user->name,
                'kategori' => \App\Services\FrontDeskService::APPLICANT_TYPES[$ticket->user->user_type] ?? $ticket->user->user_type,
                'email' => str_ends_with((string) $email, '@walkin.local') ? null : $email,
                'whatsapp' => $ticket->user->whatsapp_number,
            ] : null,
            'selesai' => $ticket->actual_completion_date?->toDateString(),
            'tenggat' => \App\Support\TicketProgress::deadline($ticket),
            'tahapan' => collect(\App\Support\TicketProgress::steps($ticket))->map(fn (array $step) => [
                'kunci' => $step['key'], 'label' => $step['label'], 'keadaan' => $step['state'], 'waktu' => $step['at']?->toIso8601String(),
            ]),
            'persetujuan' => $ticket->approval_required ? [
                'status' => $ticket->approval_status,
                'status_label' => StatusBadge::for($ticket->approval_status, 'approval')['label'],
                'oleh' => $ticket->approver?->name,
                'waktu' => $ticket->approved_at?->toIso8601String(),
                'tanda_tangan' => $ticket->signature_type ? ServiceDisposition::signatureTypeLabel($ticket->signature_type) : null,
                'unit_tujuan' => $ticket->disposition_recipients ? ServiceDisposition::recipients($ticket->disposition_recipients) : null,
                'catatan' => $ticket->approval_notes,
            ] : null,
            'kategori_dipilih_petugas' => (bool) $ticket->incoming_category,
            'riwayat_kategori' => collect(IncomingCategory::history($ticket))->map(fn (array $entry) => [
                'dari' => $entry['from'], 'ke' => $entry['to'], 'waktu' => $entry['at']?->toIso8601String(),
                'oleh' => $entry['actor'], 'alasan' => $entry['reason'], 'dipilih_petugas' => $entry['manual'],
            ]),
            'riwayat_status' => $ticket->statusHistories->map(fn ($row) => [
                'dari' => $row->from_status,
                'ke' => $row->to_status,
                'label' => $row->label(),
                'oleh' => $row->changer?->name,
                'waktu' => $row->changed_at?->toIso8601String(),
                'lama_status_sebelumnya' => $row->previousDuration(),
            ]),
            'berkas' => $ticket->files->map(fn ($file) => [
                'nama' => $file->file_name,
                'diunggah' => $file->created_at?->toIso8601String(),
                'unduh' => route('documents.ticket-file', $file),
            ]),
            'hasil_layanan' => $ticket->output ? [
                'keterangan' => $ticket->output->output_description,
                'unduh' => route('documents.ticket-output', $ticket->output),
            ] : null,
            'siap_diambil' => (bool) $ticket->ready_for_pickup,
            'langkah_workflow' => $ticket->workflowSteps->map(fn ($step) => [
                'nama' => $step->workflowStep?->name,
                'selesai' => $step->completed_at?->toIso8601String(),
            ]),
            'aksi' => [
                'ubah_status' => $user->can('update', $ticket) ? array_keys($services->nextStatuses($ticket, $user)) : [],
                'pilih_kategori' => $user->can('update', $ticket) && \App\Services\TicketService::canChooseCategory($ticket),
                'tambah_catatan' => $user->can('update', $ticket),
            ],
        ]);
    }

    /**
     * PATCH /api/tiket/{ticket:ticket_number}/status {status, catatan}: same
     * rules as the panel (allowed transitions, reason for rejection, no
     * completion before the leader's disposition). The applicant is notified.
     */
    public function updateStatus(Request $request, Ticket $ticket): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->can('update', $ticket), 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(\App\Services\TicketService::OFFICER_STATUSES))],
            'catatan' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        try {
            app(\App\Services\TicketService::class)->changeStatus($ticket, $data['status'], $data['catatan'], $user);
        } catch (\App\Exceptions\TicketActionException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status_diizinkan' => array_keys(app(\App\Services\TicketService::class)->nextStatuses($ticket->fresh(), $user)),
            ], 422);
        }

        $ticket->refresh()->load(['user:id,name,whatsapp_number', 'service:id,name', 'assignedTo:id,name', 'latestStatusHistory']);

        return response()->json(['message' => 'Status menjadi ' . TicketLabels::status($ticket->status) . '.', 'data' => self::item($ticket)]);
    }

    /** PUT /api/tiket/{ticket:ticket_number}/petugas {petugas_id, catatan?}: assign the responsible officer. */
    public function assign(Request $request, Ticket $ticket): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->can('update', $ticket), 403);

        $data = $request->validate([
            'petugas_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $staff = \App\Models\User::assignable()->find($data['petugas_id']);
        if (! $staff) {
            return response()->json(['message' => 'Pengguna ini bukan petugas aktif.', 'errors' => ['petugas_id' => ['Pilih petugas aktif.']]], 422);
        }

        app(\App\Services\TicketService::class)->assign($ticket, $staff, $user, $data['catatan'] ?? null);
        $ticket->refresh()->load(['user:id,name,whatsapp_number', 'service:id,name', 'assignedTo:id,name', 'latestStatusHistory']);

        return response()->json(['message' => "Tiket ditugaskan kepada {$staff->name}.", 'data' => self::item($ticket)]);
    }

    /**
     * POST /api/tiket/{ticket:ticket_number}/hasil (multipart: berkas, keterangan?): the
     * service product. Replaces an earlier one and completes the ticket.
     */
    public function uploadOutput(Request $request, Ticket $ticket): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->can('update', $ticket), 403);

        $data = $request->validate([
            'berkas' => ['required', 'file', 'max:20480', 'mimes:' . \App\Support\TicketDocuments::MIMES],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            app(\App\Services\TicketService::class)->uploadOutput(
                $ticket,
                \App\Support\TicketDocuments::store($data['berkas'], 'ticket-outputs'),
                $data['berkas']->getClientOriginalName(),
                $user,
                $data['keterangan'] ?? null,
            );
        } catch (\App\Exceptions\TicketActionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $ticket->refresh()->load(['user:id,name,whatsapp_number', 'service:id,name', 'assignedTo:id,name', 'latestStatusHistory']);

        return response()->json(['message' => 'Hasil layanan diunggah.', 'data' => self::item($ticket)], 201);
    }

    /** POST /api/tiket/{ticket:ticket_number}/serah-terima: the product is handed over at the counter. */
    public function handOver(Request $request, Ticket $ticket): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->can('handOver', $ticket), 403);

        try {
            app(\App\Services\TicketService::class)->handOver($ticket, $user);
        } catch (\App\Exceptions\TicketActionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $ticket->refresh()->load(['user:id,name,whatsapp_number', 'service:id,name', 'assignedTo:id,name', 'latestStatusHistory']);

        return response()->json(['message' => 'Produk layanan diserahkan kepada pemohon.', 'data' => self::item($ticket)]);
    }

    /** GET /api/tiket/{ticket:ticket_number}/catatan: follow-up notes, newest first. */
    public function notes(Request $request, Ticket $ticket): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->isStaff() && $user->can('view', $ticket), 403);

        return response()->json([
            'data' => $ticket->logs()->whereIn('action', ['note_added', 'applicant_note'])->with('performer:id,name')
                ->latest()->latest('id')->get()->map(fn (\App\Models\TicketLog $log) => self::note($log)),
            'jenis' => \App\Services\TicketService::FOLLOW_UP_TYPES,
        ]);
    }

    /** POST /api/tiket/{ticket:ticket_number}/catatan {catatan, jenis?, tampilkan_ke_pemohon?, tindak_lanjut_berikutnya?} */
    public function addNote(Request $request, Ticket $ticket): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->can('update', $ticket), 403);

        $data = $request->validate([
            'catatan' => ['required', 'string', 'min:5', 'max:1000'],
            'jenis' => ['nullable', Rule::in(array_keys(\App\Services\TicketService::FOLLOW_UP_TYPES))],
            'tampilkan_ke_pemohon' => ['nullable', 'boolean'],
            'tindak_lanjut_berikutnya' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        try {
            $log = app(\App\Services\TicketService::class)->addNote(
                $ticket, $data['catatan'], $user, $data['jenis'] ?? 'internal',
                (bool) ($data['tampilkan_ke_pemohon'] ?? false), $data['tindak_lanjut_berikutnya'] ?? null,
            );
        } catch (\App\Exceptions\TicketActionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Catatan disimpan.', 'data' => self::note($log->load('performer:id,name'))], 201);
    }

    private static function note(\App\Models\TicketLog $log): array
    {
        $type = $log->metadata['follow_up_type'] ?? null;

        return [
            'id' => $log->id,
            'catatan' => $log->notes,
            'jenis' => $type,
            'jenis_label' => $type ? (\App\Services\TicketService::FOLLOW_UP_TYPES[$type] ?? $type) : null,
            'tampil_ke_pemohon' => $log->action === 'applicant_note',
            'tindak_lanjut_berikutnya' => $log->metadata['next_follow_up_at'] ?? null,
            'oleh' => $log->performer?->name,
            'waktu' => $log->created_at?->toIso8601String(),
        ];
    }

    /**
     * PUT /api/tiket/{ticket:ticket_number}/kategori {kategori, alasan}:
     * kategori is disposisi|tembusan|koordinasi|arahan, or "otomatis" to
     * follow the leader's instruction again. Both fields are required.
     */
    public function updateCategory(Request $request, Ticket $ticket): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->can('update', $ticket), 403);

        $data = $request->validate([
            'kategori' => ['required', Rule::in([...array_keys(IncomingCategory::CATEGORIES), 'otomatis'])],
            'alasan' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'kategori.required' => 'Pilih kategori layanan masuk.',
            'kategori.in' => 'Kategori harus salah satu dari: disposisi, tembusan, koordinasi, arahan, atau otomatis.',
            'alasan.required' => 'Alasan perubahan kategori wajib diisi.',
            'alasan.min' => 'Alasan minimal 5 karakter.',
        ]);

        try {
            app(\App\Services\TicketService::class)->setIncomingCategory($ticket, $data['kategori'] === 'otomatis' ? null : $data['kategori'], $user, $data['alasan']);
        } catch (\App\Exceptions\TicketActionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $ticket->refresh();

        return response()->json([
            'message' => 'Kategori menjadi ' . IncomingCategory::label(IncomingCategory::of($ticket)) . '.',
            'kategori_masuk' => IncomingCategory::of($ticket),
            'dipilih_petugas' => (bool) $ticket->incoming_category,
            'kategori_dari_disposisi' => IncomingCategory::derived($ticket),
        ]);
    }

    /** GET /api/tiket/{ticket:ticket_number}/riwayat-kategori: initial category, then every change (oldest first). */
    public function categoryHistory(Request $request, Ticket $ticket): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->isStaff() && $user->can('view', $ticket), 403);

        return response()->json([
            'nomor_tiket' => $ticket->ticket_number,
            'kategori_masuk' => IncomingCategory::of($ticket),
            'kategori_dari_disposisi' => IncomingCategory::derived($ticket),
            'dipilih_petugas' => (bool) $ticket->incoming_category,
            'riwayat' => collect(IncomingCategory::history($ticket))->map(fn (array $entry) => [
                'dari' => $entry['from'],
                'dari_label' => $entry['from'] ? IncomingCategory::label($entry['from']) : null,
                'ke' => $entry['to'],
                'ke_label' => IncomingCategory::label($entry['to']),
                'waktu' => $entry['at']?->toIso8601String(),
                'oleh' => $entry['actor'],
                'alasan' => $entry['reason'],
                'dipilih_petugas' => $entry['manual'],
            ]),
        ]);
    }

    /** The filters of the list (everything but the tab). */
    private function filter(Builder $query, array $filters): Builder
    {
        [$from, $until] = TicketResource::dateRange([
            'period' => $filters['periode'] ?? (isset($filters['dari']) || isset($filters['sampai']) ? 'custom' : null),
            'from' => $filters['dari'] ?? null,
            'until' => $filters['sampai'] ?? null,
        ]);

        return $query
            ->search($filters['q'] ?? null)
            ->when($filters['status'] ?? null, fn (Builder $q, array $statuses) => $q->whereIn('status', $statuses))
            ->when($filters['layanan'] ?? null, fn (Builder $q, array $services) => $q->whereIn('service_id', $services))
            ->submittedBetween($from?->toDateString(), $until?->toDateString())
            ->when($filters['kategori'] ?? null, fn (Builder $q, string $category) => $q->incomingCategory($category))
            ->when($filters['unit'] ?? null, fn (Builder $q, string $unit) => $q->forwardedTo([$unit]))
            ->when($filters['petugas'] ?? null, fn (Builder $q, int $officer) => $q->where('assigned_to_id', $officer))
            ->when($filters['jalur'] ?? null, fn (Builder $q, string $mode) => $q->where('mode', $mode))
            ->when($filters['prioritas'] ?? null, fn (Builder $q, string $priority) => $q->where('priority', $priority))
            ->when(array_key_exists('terlambat', $filters) && $filters['terlambat'] !== null,
                fn (Builder $q) => $filters['terlambat'] ? $q->overdue() : $q->whereNot(fn (Builder $q) => $q->overdue()));
    }

    /** One row of the list. */
    public static function item(Ticket $ticket): array
    {
        $badge = StatusBadge::for($ticket->status);
        $category = IncomingCategory::of($ticket);

        return [
            'nomor_tiket' => $ticket->ticket_number,
            'layanan' => $ticket->service ? ['id' => $ticket->service->id, 'nama' => $ticket->service->name] : null,
            'pemohon' => $ticket->user ? ['nama' => $ticket->user->name, 'whatsapp' => $ticket->user->whatsapp_number] : null,
            'jalur' => $ticket->mode,
            'jalur_label' => TicketLabels::mode($ticket->mode),
            'status' => $ticket->status,
            'status_label' => $badge['label'],
            'status_warna' => $badge['color'],
            'status_sejak' => $ticket->statusSince()->toIso8601String(),
            'prioritas' => $ticket->priority,
            'prioritas_label' => TicketLabels::priority($ticket->priority),
            'kategori_masuk' => $category,
            'kategori_masuk_label' => IncomingCategory::label($category),
            'unit_tujuan' => $ticket->disposition_recipients ?? [],
            'petugas' => $ticket->assignedTo ? ['id' => $ticket->assignedTo->id, 'nama' => $ticket->assignedTo->name] : null,
            'diajukan' => $ticket->created_at?->toIso8601String(),
            'target_selesai' => $ticket->estimated_completion_date?->toDateString(),
            'terlambat' => $ticket->isOverdue(),
            'tautan' => TicketResource::getUrl('view', ['record' => $ticket], panel: 'admin'),
        ];
    }
}
