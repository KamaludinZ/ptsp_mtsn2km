<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\SkNumberLimitException;
use App\Exceptions\SkVersionConflictException;
use App\Exceptions\TicketActionException;
use App\Http\Controllers\Controller;
use App\Models\SuratKeputusan;
use App\Models\Ticket;
use App\Models\User;
use App\Services\JenisSkService;
use App\Services\SkReportService;
use App\Services\SuratKeputusanService;
use App\Support\ActiveRoles;
use App\Support\RegisterSkAccess;
use App\Support\SkRegisterRecap;
use App\Support\RoleAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Register Penomoran SK for Tata Usaha tools; access via SuratKeputusanPolicy. */
class SuratKeputusanController extends Controller
{
    public function __construct(private SuratKeputusanService $sk)
    {
    }

    /** GET /api/penomoran-sk: the register, filtered and paginated, newest number first (as the panel table). */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SuratKeputusan::class);

        $filters = $this->filters($request);
        $page = $this->ordered(SuratKeputusan::register($filters)->with(['pembuat:id,name', 'ticket:id,ticket_number,service_id', 'ticket.service:id,name']), $filters)
            ->paginate($filters['per_halaman'] ?? 25)->withQueryString();
        $tahun = self::year($filters);
        // What a report of these filters holds (Unduh Laporan SK), across every page.
        $ringkasan = SuratKeputusan::register($filters)->toBase()
            ->selectRaw('count(*) filter (where berkas_scan is not null) as terarsip, count(*) filter (where berkas_scan is null) as menunggu_scan')
            ->first();

        return response()->json([
            'data' => collect($page->items())->map(fn (SuratKeputusan $sk) => self::present($sk)),
            'tahun' => $tahun,
            'periode' => $tahun === 'semua' ? 'Register semua tahun' : 'Register tahun ' . $tahun,
            'halaman' => $page->currentPage(),
            'per_halaman' => $page->perPage(),
            'total' => $page->total(),
            'halaman_terakhir' => $page->lastPage(),
            'ringkasan' => ['terarsip' => (int) $ringkasan->terarsip, 'menunggu_scan' => (int) $ringkasan->menunggu_scan],
        ]);
    }

    /**
     * GET /api/penomoran-sk/arsip: the archived scans — every SK with a scan,
     * with the file's name, type, size and uploader. Filtered as the register
     * (status is always terarsip), plus jenis_berkas (PDF, JPG, PNG) and
     * urutkan=diunggah; ringkasan totals the same filters.
     */
    public function archive(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SuratKeputusan::class);

        if (is_string($type = $request->query('jenis_berkas'))) {
            $request->merge(['jenis_berkas' => strtoupper($type)]);
        }
        $filters = $this->filters($request, [
            'jenis_berkas' => ['nullable', Rule::in(array_keys(SuratKeputusan::SCAN_TYPES))],
            'urutkan' => ['nullable', 'in:nomor_urut,tanggal_surat,diunggah'],
        ]);
        unset($filters['status']);

        $direction = $filters['arah'] ?? 'desc';
        $archived = SuratKeputusan::register($filters)->withStatus('terarsip')
            ->when($filters['jenis_berkas'] ?? null, fn ($q, string $type) => $q->where('berkas_scan_mime', SuratKeputusan::SCAN_TYPES[$type]));
        $query = (clone $archived)->with('pengunggahScan:id,name');
        $query = match ($filters['urutkan'] ?? 'nomor_urut') {
            'diunggah' => $query->orderBy('scan_diunggah_pada', $direction)->orderBy('id', $direction),
            'tanggal_surat' => $query->orderBy('tanggal_surat', $direction)->orderBy('tahun', $direction)->orderBy('nomor_urut', $direction),
            default => $query->orderBy('tahun', $direction)->orderBy('nomor_urut', $direction),
        };
        $page = $query->paginate($filters['per_halaman'] ?? 25)->withQueryString();

        return response()->json([
            'data' => collect($page->items())->map(fn (SuratKeputusan $sk) => [
                ...$sk->only(['id', 'nomor_sk', 'nomor_urut', 'tahun', 'perihal', 'jenis_sk', 'nama_peminta']),
                'tanggal_surat' => $sk->tanggal_surat?->toDateString(),
                'berkas' => self::scanDetails($sk),
            ]),
            'tahun' => ($filters['tahun'] ?? null) === 'semua' ? 'semua' : (int) ($filters['tahun'] ?? now()->year),
            'ringkasan' => [
                'total_ukuran' => (int) (clone $archived)->sum('berkas_scan_ukuran'),
                // Same filters, still without a scan (jenis_berkas does not apply).
                'menunggu_scan' => SuratKeputusan::register([...$filters, 'status' => 'menunggu_scan'])->count(),
            ],
            'halaman' => $page->currentPage(),
            'per_halaman' => $page->perPage(),
            'total' => $page->total(),
            'halaman_terakhir' => $page->lastPage(),
        ]);
    }

    /**
     * GET /api/penomoran-sk/{suratKeputusan}: Rincian SK — the SK, its scan
     * archive, who recorded and last changed it, and what the caller may do
     * with it.
     */
    public function show(Request $request, SuratKeputusan $suratKeputusan): JsonResponse
    {
        $this->authorize('view', $suratKeputusan);

        return response()->json(self::detail($suratKeputusan->load(['pembuat:id,name', 'pengunggahScan:id,name']), $request->user()));
    }

    /**
     * POST /api/penomoran-sk: ask for the next SK number of the year of
     * tanggal_surat. The signed scan may come along (multipart berkas_scan,
     * as the panel's Minta Nomor SK form) or follow later.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', SuratKeputusan::class);

        // Idempotency-Key: the same request sent again gets the SK already given, not a second number.
        $kunci = $request->header('Idempotency-Key');
        if ($kunci !== null && ! preg_match('/^[A-Za-z0-9._:-]{8,100}$/', $kunci)) {
            return response()->json(['message' => 'Idempotency-Key harus 8–100 karakter: huruf, angka, titik, garis bawah, titik dua, atau tanda hubung.'], 422);
        }
        if ($given = SuratKeputusanService::givenFor($request->user(), $kunci)) {
            return self::replayed($given);
        }

        $data = $this->validateSk($request);
        $data['ticket_id'] = $data['tiket'] ?? null;
        $data['kunci_permintaan'] = $kunci;
        if ($file = $data['berkas_scan'] ?? null) {
            $data = [...$data, ...SuratKeputusanService::storeScan($file)];
        }

        try {
            $sk = $this->sk->reserve(Carbon::parse($data['tanggal_surat']), $request->user(), $data);
        } catch (\Throwable $e) {
            // No number, no scan left behind.
            if (isset($data['berkas_scan'])) {
                Storage::disk('local')->delete($data['berkas_scan']);
            }
            // The same key raced in: the other request took the number.
            if ($e instanceof \Illuminate\Database\UniqueConstraintViolationException && ($given = SuratKeputusanService::givenFor($request->user(), $kunci))) {
                return self::replayed($given);
            }
            if ($e instanceof SkNumberLimitException) {
                return response()->json(['message' => $e->getMessage(), 'coba_lagi_dalam_detik' => $e->retryAfter], 429)
                    ->header('Retry-After', (string) $e->retryAfter);
            }
            if ($e instanceof TicketActionException) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            throw $e;
        }

        return response()->json(self::present($sk->load('pembuat:id,name')), 201);
    }

    /** PUT /api/penomoran-sk/{suratKeputusan}: correct an SK; the number stays. */
    public function update(Request $request, SuratKeputusan $suratKeputusan): JsonResponse
    {
        $this->authorize('update', $suratKeputusan);

        if ($conflict = $this->staleVersion($request, $suratKeputusan)) {
            return $conflict;
        }
        $data = $this->validateSk($request, $suratKeputusan);

        try {
            $sk = $this->sk->describe($suratKeputusan, $data);
        } catch (TicketActionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(self::present($sk->load('pembuat:id,name')));
    }

    /** tiket: an existing ticket the officer may ask an SK number for. */
    private static function ticketRules(Request $request): array
    {
        return ['bail', 'nullable', 'integer', Rule::exists('tickets', 'id')->whereNull('deleted_at'), function (string $attribute, mixed $value, \Closure $fail) use ($request) {
            if (! $request->user()->can('createForTicket', [SuratKeputusan::class, Ticket::find($value)])) {
                $fail('Anda tidak dapat meminta nomor SK untuk permohonan ini.');
            }
        }];
    }

    /**
     * POST /api/penomoran-sk/kumpulan {sk: [{tanggal_surat, perihal, jenis_sk, nama_peminta, keterangan?}, ...], tiket?}:
     * several SK of one year numbered one after another, all or none (SuratKeputusanService::reserveMany).
     */
    public function storeMany(Request $request): JsonResponse
    {
        $this->authorize('create', SuratKeputusan::class);

        // As Master Persuratan spells each jenis, and perihal without "tentang".
        $request->merge(['sk' => collect((array) $request->input('sk'))->map(fn ($item) => is_array($item) ? [
            ...$item,
            'perihal' => is_string($item['perihal'] ?? null) ? preg_replace('/^tentang\s+/iu', '', Str::squish($item['perihal'])) : ($item['perihal'] ?? null),
            'nama_peminta' => is_string($item['nama_peminta'] ?? null) ? Str::squish($item['nama_peminta']) : ($item['nama_peminta'] ?? null),
            'jenis_sk' => is_string($item['jenis_sk'] ?? null) ? (JenisSkService::resolve($item['jenis_sk']) ?? $item['jenis_sk']) : ($item['jenis_sk'] ?? null),
        ] : $item)->all()]);

        $data = $request->validate([
            'sk' => ['required', 'array', 'min:1', 'max:' . SuratKeputusanService::MAX_PER_MINUTE],
            'sk.*.tanggal_surat' => ['required', 'date', 'after_or_equal:2000-01-01', 'before_or_equal:2100-12-31'],
            'sk.*.jenis_sk' => ['required', 'string', Rule::in(JenisSkService::allowed())],
            'sk.*.perihal' => ['required', 'string', 'max:255'],
            'sk.*.nama_peminta' => ['required', 'string', 'max:255'],
            'sk.*.keterangan' => ['nullable', 'string', 'max:1000'],
            'tiket' => self::ticketRules($request),
        ], [
            'sk.required' => 'Kirim daftar SK (sk).',
            'sk.max' => 'Satu kumpulan berisi paling banyak ' . SuratKeputusanService::MAX_PER_MINUTE . ' SK.',
            'sk.*.jenis_sk.in' => JenisSkService::MESSAGE,
            'sk.*.perihal.required' => 'Isi perihal setiap SK.',
            'sk.*.nama_peminta.required' => 'Isi nama peminta setiap SK.',
        ]);
        $items = array_map(fn (array $item) => [...$item, 'ticket_id' => $data['tiket'] ?? null], $data['sk']);

        try {
            $taken = $this->sk->reserveMany($request->user(), $items);
        } catch (SkNumberLimitException $e) {
            return response()->json(['message' => $e->getMessage(), 'coba_lagi_dalam_detik' => $e->retryAfter], 429)
                ->header('Retry-After', (string) $e->retryAfter);
        } catch (TicketActionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $first = reset($taken);
        $last = end($taken);

        return response()->json([
            'jumlah' => count($taken),
            'nomor' => $first->nomor_urut === $last->nomor_urut ? $first->nomor_sk : $first->nomor_urut . '–' . $last->nomor_sk,
            'data' => array_map(fn (SuratKeputusan $sk) => self::present($sk->load('pembuat:id,name')), $taken),
        ], 201);
    }

    /** The SK already given for an Idempotency-Key, sent again as it is now. */
    private static function replayed(SuratKeputusan $sk): JsonResponse
    {
        return response()->json(self::present($sk->load('pembuat:id,name')), 200)->header('Idempotent-Replayed', 'true');
    }

    /**
     * Optimistic lock for a correction: `versi` (body or query) or an
     * If-Match header with the versi the client read; 409 with the SK as it
     * is now when another officer changed it meanwhile. Without either, no check.
     */
    private function staleVersion(Request $request, SuratKeputusan $sk): ?JsonResponse
    {
        $versi = $request->input('versi') ?? trim(str_replace('W/', '', (string) $request->header('If-Match')), '" ');

        try {
            SuratKeputusanService::assertVersion($sk, $versi === '' ? null : $versi);
        } catch (SkVersionConflictException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'versi_sekarang' => $sk->versi,
                'data' => self::present($sk->load('pembuat:id,name')),
            ], 409);
        }

        return null;
    }

    /** GET /api/penomoran-sk/{suratKeputusan}/riwayat: the SK's history from Log & Audit, oldest first. */
    public function history(SuratKeputusan $suratKeputusan): JsonResponse
    {
        $this->authorize('view', $suratKeputusan);

        return response()->json([
            'nomor_sk' => $suratKeputusan->nomor_sk,
            'versi' => $suratKeputusan->versi,
            'data' => collect(SuratKeputusanService::history($suratKeputusan))
                ->map(fn (array $entry) => [...$entry, 'pada' => $entry['pada']->toIso8601String()])->all(),
        ]);
    }

    /**
     * GET /api/penomoran-sk/{suratKeputusan}/scan: the archived scan itself,
     * as its checked type and under its own name; unduh=1 for a download
     * (attachment) instead of showing it. 404 while the SK has no scan.
     */
    public function downloadScan(Request $request, SuratKeputusan $suratKeputusan): StreamedResponse
    {
        $this->authorize('view', $suratKeputusan);

        return SuratKeputusanService::scanResponse($suratKeputusan, $request->boolean('unduh'));
    }

    /** POST /api/penomoran-sk/{suratKeputusan}/scan: the signed scan that follows later (multipart berkas_scan), replacing an earlier one. */
    public function uploadScan(Request $request, SuratKeputusan $suratKeputusan): JsonResponse
    {
        $this->authorize('update', $suratKeputusan);

        if ($conflict = $this->staleVersion($request, $suratKeputusan)) {
            return $conflict;
        }
        $file = $request->validate(
            ['berkas_scan' => ['required', ...self::scanRules()]],
            ['berkas_scan.required' => 'Pilih file scan SK.', 'berkas_scan.file' => 'Kirim scan SK sebagai file.', ...self::SCAN_MESSAGES],
        )['berkas_scan'];

        return response()->json(self::present($this->sk->attachScan($suratKeputusan, $file)->load('pembuat:id,name')));
    }

    /** DELETE /api/penomoran-sk/{suratKeputusan}/scan: take the scan off; the SK waits for one again. */
    public function removeScan(Request $request, SuratKeputusan $suratKeputusan): JsonResponse
    {
        $this->authorize('update', $suratKeputusan);

        if ($conflict = $this->staleVersion($request, $suratKeputusan)) {
            return $conflict;
        }

        return response()->json(self::present($this->sk->removeScan($suratKeputusan)->load('pembuat:id,name')));
    }

    /** DELETE /api/penomoran-sk/{suratKeputusan}: administrators only, with a reason for Log & Audit. */
    public function destroy(Request $request, SuratKeputusan $suratKeputusan): JsonResponse
    {
        $this->authorize('delete', $suratKeputusan);

        $alasan = $request->validate(
            ['alasan' => ['required', 'string', 'min:5', 'max:500']],
            ['alasan.required' => 'Tulis alasan penghapusan; dicatat di Log & Audit.'],
        )['alasan'];

        $this->sk->delete($suratKeputusan, $request->user(), Str::squish($alasan));

        return response()->json(null, 204);
    }

    /**
     * The SK fields: all required when asking for a number (with an optional
     * scan), any of them when correcting. tanggal_surat stays within the years
     * the register can be filtered by. jenis_sk comes from Master Persuratan
     * (an SK may keep the jenis it was saved with after that jenis is
     * switched off).
     *
     * @return array<string, mixed>
     */
    private function validateSk(Request $request, ?SuratKeputusan $sk = null): array
    {
        $request->merge(collect($request->only(['perihal', 'nama_peminta', 'jenis_sk']))
            ->map(fn ($value) => is_string($value) ? Str::squish($value) : $value)
            // The SK title already reads "tentang ...": keep only the subject.
            ->map(fn ($value, string $key) => $key === 'perihal' && is_string($value) ? preg_replace('/^tentang\s+/iu', '', $value) : $value)
            // As Master Persuratan spells it, whatever case the client typed.
            ->map(fn ($value, string $key) => $key === 'jenis_sk' && is_string($value) ? (JenisSkService::resolve($value, $sk?->jenis_sk) ?? $value) : $value)
            ->all());

        $required = $sk ? 'sometimes' : 'required';

        return $request->validate([
            'tanggal_surat' => [$required, 'date', 'after_or_equal:2000-01-01', 'before_or_equal:2100-12-31'],
            'jenis_sk' => [$required, 'string', Rule::in(JenisSkService::allowed($sk?->jenis_sk))],
            'perihal' => [$required, 'string', 'max:255'],
            'nama_peminta' => [$required, 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            ...($sk ? [] : [
                'berkas_scan' => ['nullable', ...self::scanRules()],
                // Permohonan terkait: a PTSP ticket the officer works (SuratKeputusanPolicy::createForTicket).
                'tiket' => self::ticketRules($request),
            ]),
        ], [
            'tiket.exists' => 'Permohonan (tiket) tidak ditemukan.',
            'tanggal_surat.required' => 'Pilih tanggal surat; tahunnya menentukan nomor SK.',
            'tanggal_surat.after_or_equal' => 'Tanggal surat paling awal 1 Januari 2000.',
            'tanggal_surat.before_or_equal' => 'Tanggal surat paling akhir 31 Desember 2100.',
            ...self::SCAN_MESSAGES,
            'jenis_sk.required' => 'Pilih jenis SK.',
            'jenis_sk.in' => JenisSkService::MESSAGE,
            'perihal.required' => 'Isi perihal SK.',
            'nama_peminta.required' => 'Isi nama peminta nomor.',
        ]);
    }

    /** A scan as the panel's Unggah Scan SK takes it: PDF, JPG or PNG up to 10 MB, checked by content. */
    private static function scanRules(): array
    {
        return ['file', 'mimetypes:' . implode(',', SuratKeputusan::SCAN_MIME_TYPES), 'max:' . SuratKeputusan::SCAN_MAX_SIZE];
    }

    private const SCAN_MESSAGES = [
        'berkas_scan.mimetypes' => 'Scan SK harus berupa PDF, JPG, atau PNG.',
        'berkas_scan.max' => 'Ukuran scan paling besar 10 MB.',
    ];

    /** GET /api/penomoran-sk/saringan: the choices of each register filter. */
    public function filterOptions(): JsonResponse
    {
        $this->authorize('viewAny', SuratKeputusan::class);

        return response()->json([
            'tahun' => SuratKeputusanService::years(),
            'jenis_sk' => SuratKeputusanService::jenisOptions(),
            'status' => collect(SuratKeputusan::STATUS)->map(fn (string $label, string $value) => ['nilai' => $value, 'label' => $label])->values(),
            // Officers who took a number, for ?petugas=.
            'petugas' => User::whereIn('id', SuratKeputusan::query()->whereNotNull('pembuat_id')->select('pembuat_id'))
                ->orderBy('name')->get(['id', 'name'])
                ->map(fn (User $user) => ['id' => $user->id, 'nama' => $user->name]),
        ]);
    }

    /**
     * GET /api/penomoran-sk/unduh/{excel|pdf}?…: Unduh Laporan SK for API clients, the
     * register of the list's filters (every page, in the list's order) as a
     * file; logged in Log & Audit like the panel's downloads.
     */
    public function download(Request $request, string $format): Response
    {
        $this->authorize('viewAny', SuratKeputusan::class);

        $filters = $this->filters($request);
        $tahun = self::year($filters);
        $count = SuratKeputusan::register($filters)->count();

        if ($count === 0) {
            return response()->json(['message' => 'Tidak ada SK untuk diunduh: ' . SkReportService::emptyMessage($tahun)], 422);
        }
        if ($format === 'pdf' && $refusal = SkReportService::pdfRefusal($count)) {
            return response()->json(['message' => $refusal, 'jumlah' => $count, 'batas_pdf' => SkReportService::PDF_MAX_ROWS], 422);
        }
        if ($wait = SkReportService::downloadWait($request->user())) {
            return self::tooManyDownloads($wait);
        }

        SkReportService::log($request->user(), SkReportService::FORMATS[$format], $count, $tahun, [
            'saringan' => Arr::except($filters, ['per_halaman']),
            'pencarian' => $filters['q'] ?? null,
            'sumber' => 'api',
        ]);
        $query = $this->ordered(SuratKeputusan::register($filters), $filters);

        return $format === 'pdf'
            ? SkReportService::pdf($query, SkReportService::filename($tahun), SkReportService::criteria($filters))
            : SkReportService::excel($query, SkReportService::filename($tahun));
    }

    /** GET /api/penomoran-sk/arsip/periksa?tahun=: the archived scans still on the disk and unchanged (sk:periksa-arsip). */
    public function verifyArchive(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SuratKeputusan::class);
        $year = $request->validate(['tahun' => ['nullable', 'integer', 'between:2000,2100']], ['tahun.*' => 'Tahun harus berupa tahun antara 2000 dan 2100.'])['tahun'] ?? null;

        return response()->json(['tahun' => $year !== null ? (int) $year : 'semua', ...SuratKeputusanService::verifyArchive($year !== null ? (int) $year : null)]);
    }

    /** GET /api/penomoran-sk/nomor?tahun=: status penomoran of a year (default this year): last and next number, what is left, the gaps and why. */
    public function numbering(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SuratKeputusan::class);

        return response()->json(SuratKeputusanService::numberingStatus($this->recapYear($request)));
    }

    /** GET /api/penomoran-sk/rekap?tahun=: Rekap Register SK of one year (default this year), as the panel's recap page. */
    public function recap(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SuratKeputusan::class);

        return response()->json(self::recapData($this->recapYear($request)));
    }

    /** GET /api/penomoran-sk/rekap/unduh/{excel|pdf}?tahun=: the recap as a file, logged, within the download limit. */
    public function downloadRecap(Request $request, string $format): Response
    {
        $this->authorize('viewAny', SuratKeputusan::class);

        $recap = SkRegisterRecap::build($this->recapYear($request));
        if ($wait = SkReportService::downloadWait($request->user())) {
            return self::tooManyDownloads($wait);
        }
        SkReportService::logRecap($request->user(), SkReportService::FORMATS[$format], $recap, 'api');

        return SkReportService::recapFile($recap, $format);
    }

    private function recapYear(Request $request): int
    {
        return (int) ($request->validate(
            ['tahun' => ['nullable', 'integer', 'between:2000,2100']],
            ['tahun.*' => 'Tahun harus berupa tahun antara 2000 dan 2100.'],
        )['tahun'] ?? now()->year);
    }

    /** The recap as JSON, with the days after which a missing scan counts as overdue. */
    private static function recapData(int $year): array
    {
        $recap = SkRegisterRecap::build($year);

        return [...$recap, 'batas_hari_scan' => SkRegisterRecap::SCAN_DUE_DAYS];
    }

    private static function tooManyDownloads(int $wait): JsonResponse
    {
        return response()->json(['message' => SkReportService::downloadWaitMessage($wait), 'coba_lagi_dalam_detik' => $wait], 429)
            ->header('Retry-After', (string) $wait);
    }

    /** The list's order: urutkan (nomor_urut, tanggal_surat), arah (default newest first). */
    private function ordered(Builder $query, array $filters): Builder
    {
        $direction = $filters['arah'] ?? 'desc';

        return ($filters['urutkan'] ?? 'nomor_urut') === 'tanggal_surat'
            ? $query->orderBy('tanggal_surat', $direction)->orderBy('tahun', $direction)->orderBy('nomor_urut', $direction)
            : $query->orderBy('tahun', $direction)->orderBy('nomor_urut', $direction);
    }

    /** The year of the filters: a number (default this year) or "semua". */
    private static function year(array $filters): int|string
    {
        return ($filters['tahun'] ?? null) === 'semua' ? 'semua' : (int) ($filters['tahun'] ?? now()->year);
    }

    /** GET /api/tiket/{ticket}/sk: the SK asked for from this ticket, in number order, every year. */
    public function forTicket(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);
        $this->authorize('viewAny', SuratKeputusan::class);

        $sk = $ticket->suratKeputusan()->with(['pembuat:id,name', 'ticket:id,ticket_number,service_id', 'ticket.service:id,name'])
            ->orderBy('tahun')->orderBy('nomor_urut')->get();

        return response()->json([
            'tiket' => ['id' => $ticket->id, 'nomor' => $ticket->ticket_number],
            'data' => $sk->map(fn (SuratKeputusan $row) => self::present($row))->all(),
            'total' => $sk->count(),
        ]);
    }

    /**
     * GET /api/penomoran-sk/registrasi?tiket=&tanggal_surat=&perihal=: what a
     * request for a number starts with (as Registrasi Nomor SK in the panel):
     * the fields filled in from a ticket, the number the date would get now,
     * a note for a date outside this year, and SK whose perihal reads alike.
     * Submit with POST /api/penomoran-sk (tiket, Idempotency-Key).
     */
    public function registrationDraft(Request $request): JsonResponse
    {
        $this->authorize('create', SuratKeputusan::class);

        $data = $request->validate([
            'tiket' => self::ticketRules($request),
            'tanggal_surat' => ['nullable', 'date', 'after_or_equal:2000-01-01', 'before_or_equal:2100-12-31'],
            'perihal' => ['nullable', 'string', 'max:255'],
        ], ['tiket.exists' => 'Permohonan (tiket) tidak ditemukan.']);
        $ticket = isset($data['tiket']) ? Ticket::with(['user:id,name', 'service.jenisSk'])->find($data['tiket']) : null;

        return response()->json([
            ...SuratKeputusanService::registrationDraft($ticket, $data['tanggal_surat'] ?? null, $data['perihal'] ?? null),
            'petugas' => SuratKeputusanService::officer($request->user())['nama'],
        ]);
    }

    /** GET /api/penomoran-sk/akses: what the active role may do in the register (any staff may ask). */
    public function access(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->isStaff(), 403, 'Hanya akun petugas yang memiliki peran aktif.');

        $active = ActiveRoles::inContext($user);

        $officer = SuratKeputusanService::officer($user);

        return response()->json([
            'peran_aktif' => $active ? ['nama' => $active, 'label' => RoleAccess::roleLabel($active)] : null,
            // Recorded as petugas pencatat on the next number taken.
            'petugas' => ['id' => $officer['id'], 'nama' => $officer['nama']],
            'wewenang' => RegisterSkAccess::abilities($user),
        ]);
    }

    /**
     * @param  array<string, array<int, mixed>>  $rules  Rules of one endpoint, added to or replacing the shared ones.
     * @return array<string, mixed>
     */
    private function filters(Request $request, array $rules = []): array
    {
        // jenis_sk may be one value or a list (jenis_sk[]=...).
        if (is_string($request->query('jenis_sk'))) {
            $request->merge(['jenis_sk' => [$request->query('jenis_sk')]]);
        }

        return $request->validate([
            'tahun' => $request->query('tahun') === 'semua' ? ['in:semua'] : ['nullable', 'integer', 'min:2000', 'max:2100'],
            'q' => ['nullable', 'string', 'max:100'],
            'nomor_urut' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'jenis_sk' => ['nullable', 'array', 'max:20'],
            'jenis_sk.*' => ['string', 'max:255'],
            'status' => ['nullable', Rule::in(array_keys(SuratKeputusan::STATUS))],
            'petugas' => ['nullable', 'integer', 'exists:users,id'],
            'tiket' => ['nullable', 'string', 'max:50'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            'per_halaman' => ['nullable', 'integer', 'min:1', 'max:100'],
            'urutkan' => ['nullable', 'in:nomor_urut,tanggal_surat'],
            'arah' => ['nullable', 'in:asc,desc'],
            ...$rules,
        ]);
    }

    public static function present(SuratKeputusan $sk): array
    {
        return [
            'id' => $sk->id,
            'nomor_urut' => $sk->nomor_urut,
            'nomor_sk' => $sk->nomor_sk,
            'tahun' => $sk->tahun,
            'tanggal_surat' => $sk->tanggal_surat?->toDateString(),
            'perihal' => $sk->perihal,
            'jenis_sk' => $sk->jenis_sk,
            'nama_peminta' => $sk->nama_peminta,
            'keterangan' => $sk->keterangan,
            'tiket' => $sk->ticket_id && $sk->ticket ? [
                'id' => $sk->ticket->id,
                'nomor' => $sk->ticket->ticket_number,
                'layanan' => $sk->ticket->service?->name,
            ] : null,
            'status' => $sk->status(),
            'status_label' => SuratKeputusan::STATUS[$sk->status()],
            'ada_scan' => $sk->hasScan(),
            // Days from the number to the signed scan (null while it waits), as Rekap Register SK averages it.
            'hari_sampai_terarsip' => $sk->hari_sampai_terarsip,
            'nama_scan' => $sk->scanName(),
            'petugas' => $sk->pembuat?->name,
            'peran_petugas' => $sk->peran_pembuat ? ['nama' => $sk->peran_pembuat, 'label' => RoleAccess::roleLabel($sk->peran_pembuat)] : null,
            'dicatat' => $sk->created_at?->toIso8601String(),
            'diubah' => $sk->updated_at?->toIso8601String(),
            // Send back with a correction (versi or If-Match) to refuse overwriting someone else's change.
            'versi' => $sk->versi,
        ];
    }

    /** The scan in the archive (never its stored path), or null while the SK waits for one. */
    private static function scanDetails(SuratKeputusan $sk): ?array
    {
        return $sk->hasScan() ? [
            'nama' => $sk->scanName(),
            'jenis' => $sk->scanType(),
            'mime' => $sk->berkas_scan_mime,
            'ukuran' => $sk->berkas_scan_ukuran,
            'sha256' => $sk->berkas_scan_sha256,
            'diunggah_pada' => $sk->scan_diunggah_pada?->toIso8601String(),
            'diunggah_oleh' => $sk->scan_diunggah_oleh ? ($sk->pengunggahScan?->name ?? 'Akun sudah dihapus') : null,
            'tautan_unduh' => route('api.penomoran-sk.scan.show', [$sk, 'unduh' => 1]),
        ] : null;
    }

    /** One SK as Rincian SK shows it: the register fields plus the archive, the last change and the caller's rights. */
    public static function detail(SuratKeputusan $sk, User $viewer): array
    {
        $change = SuratKeputusanService::lastChange($sk);

        return [
            ...self::present($sk),
            'petugas_label' => SuratKeputusanService::officerLabel($sk->pembuat?->name, $sk->peran_pembuat),
            'arsip' => self::scanDetails($sk),
            'perubahan_terakhir' => $change ? [
                'oleh' => $change['sistem'] ? 'Sistem' : ($change['nama'] ?? 'Akun sudah dihapus'),
                'peran' => $change['peran'] ? ['nama' => $change['peran'], 'label' => RoleAccess::roleLabel($change['peran'])] : null,
                'pada' => $change['pada']->toIso8601String(),
            ] : null,
            'wewenang' => [
                'ubah' => $viewer->can('update', $sk),
                'hapus' => $viewer->can('delete', $sk),
            ],
        ];
    }
}
