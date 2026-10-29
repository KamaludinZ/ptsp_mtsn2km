<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\TicketFile;
use App\Models\TicketLog;
use App\Services\TicketService;
use App\Support\PortalServices;
use App\Support\RichText;
use App\Support\TicketDocuments;
use App\Support\TicketLabels;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
                'unduh' => $url = route('documents.ticket-file', $file),
                ...TicketDocuments::fileType($file->file_name, $url),
            ])->values(),
            // "hasil" stays the download address for older clients; "hasil_layanan" describes the file.
            'hasil' => $ticket->output?->file_path ? route('documents.ticket-output', $ticket->output) : null,
            // Through the API (a token or the portal's session), as a file named by the ticket.
            'hasil_layanan' => $ticket->output?->file_path ? [
                'keterangan' => $ticket->output->output_description,
                'unduh' => $url = route('api.permohonan.hasil', $ticket),
                ...TicketDocuments::fileType($ticket->output->file_path, $url),
            ] : null,
            'tanda_terima' => route('tickets.receipt', $ticket),
            'riwayat' => $ticket->logs->sortBy('created_at')->values()->map(fn (TicketLog $log) => [
                'waktu' => $log->created_at?->toIso8601String(),
                'kegiatan' => TicketLabels::logAction($log->action),
                'perubahan_status' => $log->statusChange(),
                'catatan' => $log->notes,
            ]),
        ]);
    }

    /**
     * GET /api/permohonan/{ticket:ticket_number}/hasil: the request's product
     * file for its own applicant. Akses langsung pemilik akun: the sign-in is
     * the proof, so no e-mail or number is asked. ?pratinjau=1 shows a PDF in
     * the page.
     */
    public function output(Request $request, Ticket $ticket)
    {
        abort_unless($ticket->user_id === $request->user()->id, 404);
        abort_unless(TicketDocuments::exists($ticket->output?->file_path), 404, 'Hasil layanan belum tersedia');

        return $request->boolean('pratinjau')
            ? TicketDocuments::preview($ticket->output->file_path, TicketDocuments::outputName($ticket))
            : TicketDocuments::download($ticket->output->file_path, TicketDocuments::outputName($ticket));
    }

    /**
     * GET /api/permohonan/layanan?q=&kategori=&jalur=online|semua&peran=: the
     * portal's catalogue (App\Filament\Portal\Pages\ServiceCatalog), for the
     * account's applicant roles (peran_saya), or one of them (peran). By
     * default the services requested online, as before; jalur=semua adds
     * those requested at the counter.
     */
    public function services(Request $request): JsonResponse
    {
        $user = $request->user();
        $roles = PortalServices::rolesOf($user);
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'kategori' => ['nullable', 'integer'],
            'jalur' => ['nullable', Rule::in(['online', 'semua'])],
            'peran' => ['nullable', Rule::in($roles)],
        ], ['peran.in' => 'Peran itu bukan peran pemohon akun Anda.']);

        $offered = (($filters['jalur'] ?? 'online') === 'semua' ? PortalServices::offered($user) : PortalServices::requestable($user))
            ->with(['categories:id,name'])
            ->withCount(['activeTemplates as jumlah_template', 'requirements as jumlah_persyaratan'])
            ->orderBy('name')
            ->get();
        $services = $offered
            ->when($filters['q'] ?? null, fn ($all, string $term) => $all->filter(fn (Service $service) => str_contains(mb_strtolower($service->name), mb_strtolower($term))
                || str_contains(mb_strtolower((string) $service->code), mb_strtolower($term))))
            ->when($filters['kategori'] ?? null, fn ($all, int $id) => $all->filter(fn (Service $service) => $service->categories->contains('id', $id)))
            ->when($filters['peran'] ?? null, fn ($all, string $role) => $all->filter(fn (Service $service) => $service->isAvailableFor($role)))
            ->values();
        $label = fn (string $role) => ['peran' => $role, 'label' => \App\Support\RoleAccess::roleLabel($role)];
        $open = Ticket::where('user_id', $user->id)->open()->latest()->latest('id')->get(['ticket_number', 'service_id'])->unique('service_id')->keyBy('service_id');

        return response()->json([
            'data' => $services->map(fn (Service $service) => [
                'slug' => $service->slug,
                'kode' => $service->code,
                'nama' => $service->name,
                'ringkasan' => RichText::plain($service->description, 200),
                'kategori' => $service->categories->pluck('name')->values(),
                'jalur' => $service->mode,
                'bisa_diajukan_online' => in_array($service->mode, ['online', 'hybrid'], true),
                'waktu_penyelesaian' => $service->processing_time,
                'biaya' => (float) $service->fee,
                'jumlah_persyaratan' => $service->jumlah_persyaratan,
                'jumlah_template' => $service->jumlah_template,
                'template' => route('api.publik.layanan.template', $service->slug),
                'permohonan_berjalan' => $open->get($service->id)?->ticket_number,
                'untuk' => collect($roles)->filter(fn (string $role) => $service->isAvailableFor($role))->map($label)->values(),
            ]),
            'total' => $services->count(),
            'peran_saya' => collect($roles)->map($label)->values(),
            // The categories of every service listed before q and kategori, to choose from.
            'kategori' => $offered->flatMap->categories->groupBy('id')
                ->map(fn ($group) => ['id' => $group->first()->id, 'nama' => $group->first()->name, 'jumlah' => $group->count()])
                ->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE)->values(),
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

        // The service, by the account's applicant roles as the catalogue lists it; the ticket keeps its id.
        $service = PortalServices::requestable($user)->where('slug', $data['layanan'])->first();

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
