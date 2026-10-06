<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\TicketActionException;
use App\Exports\SuratKeluarExport;
use App\Http\Controllers\Controller;
use App\Models\SuratKeluar;
use App\Services\SuratKeluarService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

/** Surat keluar for Tata Usaha tools: reserve numbers, fill in letters, read the register. */
class SuratKeluarController extends Controller
{
    public function __construct(private SuratKeluarService $letters)
    {
    }

    /** GET /api/surat-keluar: the register, filtered and paginated. */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SuratKeluar::class);

        $filters = $this->filters($request);
        $page = $this->register($filters)->with('pembuat:id,name')->paginate($filters['per_halaman'] ?? 25)->withQueryString();

        return response()->json([
            'data' => collect($page->items())->map(fn (SuratKeluar $letter) => self::present($letter)),
            'halaman' => $page->currentPage(),
            'total' => $page->total(),
            'halaman_terakhir' => $page->lastPage(),
        ]);
    }

    /** GET /api/surat-keluar/ekspor?format=xlsx|pdf with the same filters. */
    public function export(Request $request)
    {
        $this->authorize('viewAny', SuratKeluar::class);

        $filters = $this->filters($request);
        $format = $request->validate(['format' => ['nullable', 'in:xlsx,pdf']])['format'] ?? 'xlsx';
        $name = 'register-surat-keluar-' . now()->format('Ymd-His');

        if ($format === 'xlsx') {
            return Excel::download(new SuratKeluarExport($this->register($filters)), "{$name}.xlsx");
        }

        $letters = $this->register($filters)->with('pembuat:id,name')->reorder('tahun')->orderBy('nomor_urut')->get();

        return Pdf::loadView('pdf.surat-keluar-register', [
            'letters' => $letters,
            'years' => $letters->pluck('tahun')->unique()->sort()->join(', '),
        ])->setPaper('a4', 'landscape')->download("{$name}.pdf");
    }

    private function filters(Request $request): array
    {
        return $request->validate([
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'q' => ['nullable', 'string', 'max:100'],
            'jenis_surat' => ['nullable', 'string', 'max:100'],
            'klasifikasi' => ['nullable', 'string', 'max:50'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            'lengkap' => ['nullable', 'boolean'],
            'per_halaman' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
    }

    private function register(array $filters): Builder
    {
        return SuratKeluar::query()
            ->where('tahun', $filters['tahun'] ?? now()->year)
            ->when($filters['q'] ?? null, function ($query, string $term) {
                $like = '%' . addcslashes($term, '%_\\') . '%';
                $query->where(fn ($q) => $q->where('nomor_surat', 'ilike', $like)->orWhere('tujuan_surat', 'ilike', $like)->orWhere('perihal', 'ilike', $like));
            })
            ->when($filters['jenis_surat'] ?? null, fn ($q, $v) => $q->where('jenis_surat', $v))
            ->when($filters['klasifikasi'] ?? null, fn ($q, $v) => $q->where('klasifikasi', $v))
            ->when($filters['dari'] ?? null, fn ($q, $d) => $q->whereDate('tanggal_surat', '>=', $d))
            ->when($filters['sampai'] ?? null, fn ($q, $d) => $q->whereDate('tanggal_surat', '<=', $d))
            ->when(isset($filters['lengkap']), fn ($q) => $filters['lengkap']
                ? $q->whereNotNull('perihal')->whereNotNull('tujuan_surat')
                : $q->where(fn ($w) => $w->whereNull('perihal')->orWhereNull('tujuan_surat')))
            ->orderByDesc('nomor_urut');
    }

    /** POST /api/surat-keluar/nomor: reserve `jumlah` consecutive numbers. */
    public function reserve(Request $request): JsonResponse
    {
        $this->authorize('create', SuratKeluar::class);

        $data = $request->validate([
            'jumlah' => ['required', 'integer', 'min:1', 'max:' . SuratKeluarService::MAX_PER_REQUEST],
            'tanggal_surat' => ['nullable', 'date'],
            ...$this->letterRules(),
        ]);

        try {
            $letters = $this->letters->reserve(
                (int) $data['jumlah'],
                Carbon::parse($data['tanggal_surat'] ?? today()),
                $request->user(),
                $this->letterFields($data),
            );
        } catch (TicketActionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'jumlah' => $letters->count(),
            'data' => $letters->map(fn (SuratKeluar $letter) => self::present($letter))->values(),
        ], 201);
    }

    /** PUT /api/surat-keluar/{suratKeluar}: fill in or correct a reserved letter. */
    public function update(Request $request, SuratKeluar $suratKeluar): JsonResponse
    {
        $this->authorize('update', $suratKeluar);

        $data = $request->validate([
            'tanggal_surat' => ['nullable', 'date'],
            ...$this->letterRules(),
            'tujuan_surat' => ['required', 'string', 'max:255'],
            'perihal' => ['required', 'string', 'max:255'],
        ]);

        try {
            $letter = $this->letters->describe($suratKeluar, $this->letterFields($data) + array_filter(['tanggal_surat' => $data['tanggal_surat'] ?? null]));
        } catch (TicketActionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(self::present($letter->load('pembuat')));
    }

    /** Validation rules for the descriptive fields of a letter. */
    private function letterRules(): array
    {
        return [
            'tujuan_surat' => ['nullable', 'string', 'max:255'],
            'perihal' => ['nullable', 'string', 'max:255'],
            'jenis_surat' => ['nullable', 'string', 'max:100'],
            'klasifikasi' => ['nullable', 'string', 'max:50'],
            'lampiran' => ['nullable', 'string', 'max:1000'],
            'tembusan' => ['nullable', 'array'],
            'tembusan.*' => ['string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /** The request's letter fields as model attributes (tembusan: one per line). */
    private function letterFields(array $data): array
    {
        $fields = array_intersect_key($data, array_flip(['tujuan_surat', 'perihal', 'jenis_surat', 'klasifikasi', 'lampiran', 'keterangan']));
        if (isset($data['tembusan'])) {
            $fields['tembusan'] = implode("\n", $data['tembusan']) ?: null;
        }

        return array_filter($fields, fn ($value) => $value !== null);
    }

    public static function present(SuratKeluar $letter): array
    {
        return [
            'id' => $letter->id,
            'nomor_urut' => $letter->nomor_urut,
            'nomor_surat' => $letter->nomor_surat,
            'tahun' => $letter->tahun,
            'tanggal_surat' => $letter->tanggal_surat?->toDateString(),
            'tujuan_surat' => $letter->tujuan_surat,
            'perihal' => $letter->perihal,
            'jenis_surat' => $letter->jenis_surat,
            'klasifikasi' => $letter->klasifikasi,
            'lampiran' => $letter->lampiran,
            'tembusan' => $letter->tembusan ? explode("\n", $letter->tembusan) : [],
            'keterangan' => $letter->keterangan,
            'pembuat' => $letter->pembuat?->name,
            'lengkap' => ! $letter->isDraft(),
        ];
    }
}
