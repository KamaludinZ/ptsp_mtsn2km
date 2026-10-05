<?php

namespace App\Http\Controllers\Api;

use App\Filament\Resources\PersuratanMasterResource;
use App\Http\Controllers\Controller;
use App\Models\PersuratanMaster;
use App\Support\Persuratan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Master Persuratan choices for letter and disposition forms. */
class PersuratanController extends Controller
{
    /** GET /api/persuratan/pilihan?jenis=tujuan_naskah|jenis_surat|tembusan|klasifikasi|instruksi_disposisi */
    public function options(Request $request): JsonResponse
    {
        abort_unless($request->user()->isStaff(), 403);

        $type = $request->validate(['jenis' => ['nullable', Rule::in(array_keys(PersuratanMaster::TYPES))]])['jenis'] ?? null;
        $types = $type ? [$type] : array_keys(PersuratanMaster::TYPES);

        return response()->json(collect($types)->mapWithKeys(fn (string $t) => [$t => Persuratan::options($t)]));
    }

    /** GET /api/persuratan/master?jenis=&q=&aktif=&per_halaman=: every entry, for managing the lists. */
    public function index(Request $request): JsonResponse
    {
        abort_unless(PersuratanMasterResource::canViewAny(), 403);

        $filters = $request->validate([
            'jenis' => ['nullable', Rule::in(array_keys(PersuratanMaster::TYPES))],
            'q' => ['nullable', 'string', 'max:100'],
            'aktif' => ['nullable', 'boolean'],
            'per_halaman' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $page = PersuratanMaster::query()
            ->when($filters['jenis'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($filters['q'] ?? null, function ($query, string $term) {
                $like = '%' . addcslashes($term, '%_\\') . '%';
                $query->where(fn ($q) => $q->where('nama', 'ilike', $like)->orWhere('kode', 'ilike', $like));
            })
            ->when(isset($filters['aktif']), fn ($q) => $q->where('is_active', (bool) $filters['aktif']))
            ->orderBy('type')->orderBy('sort')->orderBy('nama')
            ->paginate($filters['per_halaman'] ?? 25)
            ->withQueryString();

        return response()->json([
            'data' => collect($page->items())->map(fn (PersuratanMaster $m) => $this->present($m)),
            'halaman' => $page->currentPage(),
            'total' => $page->total(),
            'halaman_terakhir' => $page->lastPage(),
        ]);
    }

    /** POST /api/persuratan/master */
    public function store(Request $request): JsonResponse
    {
        abort_unless(PersuratanMasterResource::canCreate(), 403);

        $master = PersuratanMaster::create($this->validated($request));

        return response()->json($this->present($master), 201);
    }

    /** PUT /api/persuratan/master/{master} */
    public function update(Request $request, PersuratanMaster $master): JsonResponse
    {
        abort_unless(PersuratanMasterResource::canEdit($master), 403);

        $master->update($this->validated($request, $master));

        return response()->json($this->present($master));
    }

    /** DELETE /api/persuratan/master/{master}: letters that used it keep their text. */
    public function destroy(PersuratanMaster $master): JsonResponse
    {
        abort_unless(PersuratanMasterResource::canDelete($master), 403);

        $master->delete();

        return response()->json(null, 204);
    }

    private function validated(Request $request, ?PersuratanMaster $master = null): array
    {
        $type = $request->input('jenis', $master?->type);

        $data = $request->validate([
            'jenis' => [$master ? 'sometimes' : 'required', Rule::in(array_keys(PersuratanMaster::TYPES))],
            'nama' => [$master ? 'sometimes' : 'required', 'string', 'max:255',
                Rule::unique('persuratan_masters', 'nama')->where('type', $type)->ignore($master?->id)],
            'kode' => [Rule::requiredIf($type === 'klasifikasi' && ! $master?->kode), 'nullable', 'string', 'max:50'],
            'aktif' => ['sometimes', 'boolean'],
            'urutan' => ['sometimes', 'integer', 'min:0'],
        ]);

        return array_filter([
            'type' => $data['jenis'] ?? null,
            'nama' => $data['nama'] ?? null,
            'kode' => $data['kode'] ?? null,
            'is_active' => $data['aktif'] ?? null,
            'sort' => $data['urutan'] ?? null,
        ], fn ($value) => $value !== null);
    }

    private function present(PersuratanMaster $master): array
    {
        return [
            'id' => $master->id,
            'jenis' => $master->type,
            'kode' => $master->kode,
            'nama' => $master->nama,
            'nilai' => $master->value,
            'aktif' => $master->is_active,
            'urutan' => $master->sort,
        ];
    }
}
