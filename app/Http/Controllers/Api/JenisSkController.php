<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PersuratanMaster;
use App\Services\JenisSkService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Jenis SK (Master Persuratan > Jenis SK): the classifications of the SK
 * register. Authorized by PersuratanMasterPolicy, like the Master
 * Persuratan tab: back office reads, adds and edits; only administrators
 * delete.
 */
class JenisSkController extends Controller
{
    private const TYPE = 'jenis_sk';

    /**
     * GET /api/persuratan/jenis-sk?q=&aktif=&tahun=&layanan=: every jenis SK in list order, with its use in the
     * register (tahun: default this year); layanan (a PTSP service id) narrows it to that service's jenis SK bawaan.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PersuratanMaster::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'aktif' => ['nullable', 'boolean'],
            'tahun' => ['nullable', 'integer', 'between:2000,2100'],
            'layanan' => ['nullable', 'integer', 'exists:services,id'],
        ], ['tahun.*' => 'Tahun harus berupa tahun antara 2000 dan 2100.', 'layanan.*' => 'Layanan PTSP tidak ditemukan.']);
        $year = (int) ($filters['tahun'] ?? now()->year);

        $rows = PersuratanMaster::query()
            ->where('type', self::TYPE)
            ->when($filters['q'] ?? null, fn ($q, string $term) => $q->where('nama', 'ilike', '%' . addcslashes($term, '%_\\') . '%'))
            ->when(isset($filters['aktif']), fn ($q) => $q->where('is_active', (bool) $filters['aktif']))
            ->when($filters['layanan'] ?? null, fn ($q, $service) => $q->whereHas('layanan', fn ($s) => $s->whereKey($service)))
            ->orderBy('sort')->orderBy('nama')
            ->get();
        $usage = JenisSkService::usage($rows->pluck('nama'), $year);

        return response()->json([
            'data' => $rows->map(fn (PersuratanMaster $jenis) => $this->present($jenis, $usage[$jenis->nama] ?? null)),
            'tahun' => $year,
            'total' => $rows->count(),
        ]);
    }

    /** GET /api/persuratan/jenis-sk/{jenisSk} */
    public function show(PersuratanMaster $jenisSk): JsonResponse
    {
        $this->ensureJenisSk($jenisSk);
        $this->authorize('view', $jenisSk);

        return response()->json($this->present($jenisSk));
    }

    /** GET /api/persuratan/jenis-sk/{jenisSk}/statistik: SK of this jenis per year (Laporan & Evaluasi). */
    public function statistics(PersuratanMaster $jenisSk): JsonResponse
    {
        $this->ensureJenisSk($jenisSk);
        $this->authorize('view', $jenisSk);

        return response()->json([
            'id' => $jenisSk->id,
            'nama' => $jenisSk->nama,
            'aktif' => $jenisSk->is_active,
            ...JenisSkService::statistics($jenisSk),
        ]);
    }

    /** POST /api/persuratan/jenis-sk */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', PersuratanMaster::class);

        $data = $this->validated($request);
        $services = $this->services($request);
        $jenis = $this->saving(fn () => PersuratanMaster::create(['type' => self::TYPE, 'is_active' => true, 'sort' => $this->nextSort(), ...$data]));
        if ($services !== null) {
            JenisSkService::assignServices($jenis, $services);
        }

        return response()->json($this->present($jenis), 201);
    }

    /** PUT /api/persuratan/jenis-sk/{jenisSk}: SK already recorded keep the jenis they were saved with. */
    public function update(Request $request, PersuratanMaster $jenisSk): JsonResponse
    {
        $this->ensureJenisSk($jenisSk);
        $this->authorize('update', $jenisSk);

        $data = $this->validated($request, $jenisSk);
        $services = $this->services($request);
        $this->saving(fn () => $jenisSk->update($data));
        if ($services !== null) {
            JenisSkService::assignServices($jenisSk, $services);
        }

        return response()->json($this->present($jenisSk));
    }

    /** DELETE /api/persuratan/jenis-sk/{jenisSk}: administrators only; SK already recorded keep the name (see jumlah_sk). */
    public function destroy(PersuratanMaster $jenisSk): JsonResponse
    {
        $this->ensureJenisSk($jenisSk);
        $this->authorize('delete', $jenisSk);

        $jenisSk->delete();

        return response()->json(null, 204);
    }

    /** Other Master Persuratan lists are not reachable through this endpoint. */
    private function ensureJenisSk(PersuratanMaster $master): void
    {
        abort_unless($master->type === self::TYPE, 404);
    }

    /**
     * Save inside a savepoint: when another officer stored the same name a
     * moment earlier, the database (lower(btrim(nama)) per jenis_sk) refuses
     * it and the client gets the usual validation error instead of a 500.
     */
    private function saving(\Closure $save): mixed
    {
        try {
            return DB::transaction($save);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['nama' => 'Jenis SK ini sudah ada.']);
        }
    }

    private function nextSort(): int
    {
        return min(9999, (int) PersuratanMaster::where('type', self::TYPE)->max('sort') + 1);
    }

    /**
     * layanan: ids of the PTSP services that take this jenis as their default
     * (the whole list; [] clears it); null when the request leaves it out.
     *
     * @return ?array<int, int>
     */
    private function services(Request $request): ?array
    {
        if (! $request->has('layanan')) {
            return null;
        }

        return $request->validate(
            ['layanan' => ['present', 'array', 'max:200'], 'layanan.*' => ['integer', 'distinct', 'exists:services,id']],
            ['layanan.*.exists' => 'Layanan PTSP tidak ditemukan.'],
        )['layanan'];
    }

    private function validated(Request $request, ?PersuratanMaster $jenis = null): array
    {
        if (is_string($request->input('nama'))) {
            $request->merge(['nama' => Str::squish((string) $request->input('nama'))]);
        }

        $data = $request->validate([
            'nama' => ['bail', $jenis ? 'sometimes' : 'required', 'string', 'min:3', 'max:255', function (string $attribute, mixed $value, \Closure $fail) use ($jenis) {
                $taken = PersuratanMaster::where('type', self::TYPE)
                    ->whereRaw('lower(trim(nama)) = ?', [mb_strtolower((string) $value)])
                    ->when($jenis, fn ($q) => $q->whereKeyNot($jenis->getKey()))
                    ->exists();

                if ($taken) {
                    $fail('Jenis SK ini sudah ada.');
                }
            }],
            'aktif' => ['sometimes', 'boolean'],
            'urutan' => ['sometimes', 'integer', 'min:0', 'max:9999'],
        ], [
            'nama.required' => 'Nama jenis SK wajib diisi.',
            'nama.min' => 'Nama jenis SK minimal 3 karakter.',
            'nama.max' => 'Nama jenis SK paling panjang 255 karakter.',
        ]);

        return array_filter([
            'nama' => $data['nama'] ?? null,
            'is_active' => isset($data['aktif']) ? (bool) $data['aktif'] : null,
            'sort' => $data['urutan'] ?? null,
        ], fn ($value) => $value !== null);
    }

    /**
     * jumlah_sk: SK recorded under this name in every year (they keep it after
     * a rename or delete); jumlah_sk_tahun: in the year asked for (index) or this year.
     *
     * @param  ?array{semua: int, tahun: int}  $usage
     */
    private function present(PersuratanMaster $jenis, ?array $usage = null): array
    {
        $usage ??= JenisSkService::usage([$jenis->nama])[$jenis->nama];

        return [
            'id' => $jenis->id,
            'nama' => $jenis->nama,
            'aktif' => $jenis->is_active,
            'urutan' => $jenis->sort,
            'jumlah_sk' => $usage['semua'],
            'jumlah_sk_tahun' => $usage['tahun'],
            // Layanan PTSP whose SK start with this jenis.
            'layanan' => $jenis->layanan()->orderBy('name')->get(['id', 'name'])->map(fn ($service) => ['id' => $service->id, 'nama' => $service->name])->all(),
        ];
    }
}
