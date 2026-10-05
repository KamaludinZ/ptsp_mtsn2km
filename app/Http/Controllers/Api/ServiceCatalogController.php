<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\FrontDeskService;
use App\Support\ServiceDisposition;
use App\Support\TicketLabels;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Katalog Layanan for staff: every service (active or not) with its
 * configuration, unlike the public catalog that only lists what an
 * applicant may request.
 */
class ServiceCatalogController extends Controller
{
    /** GET /api/layanan-ptsp?q=&kategori=&jalur=&aktif=&untuk=&template=&per_halaman= */
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->isStaff(), 403);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'kategori' => ['nullable', 'integer'],
            'jalur' => ['nullable', Rule::in(array_keys(TicketLabels::MODES))],
            'aktif' => ['nullable', 'boolean'],
            'untuk' => ['nullable', Rule::in(array_keys(FrontDeskService::APPLICANT_TYPES))],
            'template' => ['nullable', 'boolean'],
            'per_halaman' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $page = Service::query()
            ->with('categories:id,name')
            ->withCount(['requirements', 'templates', 'tickets as open_tickets_count' => fn (Builder $q) => $q->open()])
            ->when($filters['q'] ?? null, fn (Builder $q, string $term) => $q->where(fn (Builder $q) => $q
                ->whereRaw('lower(name) like ?', ['%' . mb_strtolower($term) . '%'])
                ->orWhereRaw('lower(code) like ?', ['%' . mb_strtolower($term) . '%'])))
            ->when($filters['kategori'] ?? null, fn (Builder $q, int $category) => $q->whereHas('categories', fn ($c) => $c->whereKey($category)))
            ->when($filters['jalur'] ?? null, fn (Builder $q, string $mode) => $q->where('mode', $mode))
            ->when(isset($filters['aktif']), fn (Builder $q) => $q->where('is_active', (bool) $filters['aktif']))
            ->when($filters['untuk'] ?? null, fn (Builder $q, string $type) => $q->availableFor($type))
            ->when(isset($filters['template']), fn (Builder $q) => $filters['template'] ? $q->has('templates') : $q->doesntHave('templates'))
            ->orderBy('name')
            ->paginate($filters['per_halaman'] ?? 20)
            ->withQueryString();

        return response()->json([
            'data' => collect($page->items())->map(fn (Service $service) => self::summary($service) + [
                'jumlah_syarat' => $service->requirements_count,
                'jumlah_template' => $service->templates_count,
                'permohonan_berjalan' => $service->open_tickets_count,
            ]),
            'kategori' => ServiceCategory::orderBy('name')->get(['id', 'name'])->map(fn ($c) => ['id' => $c->id, 'nama' => $c->name]),
            'meta' => ['halaman' => $page->currentPage(), 'per_halaman' => $page->perPage(), 'total' => $page->total(), 'halaman_terakhir' => $page->lastPage()],
        ]);
    }

    /** GET /api/layanan-ptsp/{service} */
    public function show(Request $request, Service $service): JsonResponse
    {
        abort_unless($request->user()?->isStaff(), 403);

        $service->load(['categories:id,name', 'workflow.steps' => fn ($q) => $q->orderBy('step_number'), 'templates']);

        return response()->json(self::summary($service) + [
            'deskripsi' => $service->description,
            'persyaratan' => $service->getAttributes()['requirements'] ?? null,
            'berkas_persyaratan' => $service->requirements()->orderBy('id')->get()->map(fn ($r) => [
                'nama' => $r->requirement_name, 'wajib' => (bool) $r->is_required, 'keterangan' => $r->description,
            ]),
            'mekanisme' => $service->mechanism,
            'langkah_workflow' => $service->workflow?->steps->map(fn ($step) => [
                'urutan' => $step->step_number, 'nama' => $step->name, 'keterangan' => $step->description,
                'perkiraan_hari' => $step->estimated_duration_days, 'opsional' => (bool) $step->is_optional,
            ]) ?? [],
            'produk' => $service->product,
            'hasil_digital' => (bool) $service->is_digital_product,
            'penanganan_pengaduan' => $service->complaint_handling,
            'template_berkas' => $service->templates->map(fn ($template) => ServiceTemplateController::item($template)),
            'disposisi' => [
                'mode' => $service->disposition_mode,
                'mode_label' => ServiceDisposition::mode($service->disposition_mode),
                'unit_tujuan' => $service->disposition_roles ?? [],
                'unit_tujuan_label' => $service->disposition_roles ? ServiceDisposition::recipients($service->disposition_roles) : null,
                'anjuran_tanda_tangan' => $service->signature_recommendation,
            ],
            'permohonan' => [
                'berjalan' => $service->tickets()->open()->count(),
                'total' => $service->tickets()->count(),
            ],
            'tautan_portal' => $service->is_active ? route('onlineportal.service.detail', $service->slug) : null,
        ]);
    }

    /** POST /api/layanan-ptsp (admin) */
    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasRole('admin'), 403);

        $service = \Illuminate\Support\Facades\DB::transaction(fn () => $this->save(new Service(['is_active' => true]), $this->validated($request, null)));
        activity('audit')->causedBy($request->user())->performedOn($service)->log('Menambah layanan ' . $service->name);

        return $this->show($request, $service)->setStatusCode(201);
    }

    /** PATCH /api/layanan-ptsp/{service} (admin): only the fields sent are changed. */
    public function update(Request $request, Service $service): JsonResponse
    {
        abort_unless($request->user()?->hasRole('admin'), 403);

        \Illuminate\Support\Facades\DB::transaction(fn () => $this->save($service, $this->validated($request, $service)));
        activity('audit')->causedBy($request->user())->performedOn($service)->log('Mengubah layanan ' . $service->name);

        return $this->show($request, $service->fresh());
    }

    /**
     * PATCH /api/layanan-ptsp/{service}/aktif {aktif} (admin): show or hide a
     * service on the portal. Requests already in progress keep going; the
     * response says how many there are.
     */
    public function setActive(Request $request, Service $service): JsonResponse
    {
        abort_unless($request->user()?->hasRole('admin'), 403);

        $active = (bool) $request->validate(['aktif' => ['required', 'boolean']])['aktif'];
        $changed = $service->is_active !== $active;
        \App\Filament\Resources\ServiceResource::setActive($service, $active);
        $open = $service->tickets()->open()->count();

        return response()->json([
            'message' => match (true) {
                ! $changed => 'Status layanan tidak berubah.',
                $active => 'Layanan diaktifkan dan tampil kembali di portal.',
                default => 'Layanan dinonaktifkan dan tidak bisa diajukan lagi.' . ($open ? " {$open} permohonan berjalan tetap diproses." : ''),
            },
            'aktif' => $service->is_active,
            'berubah' => $changed,
            'permohonan_berjalan' => $open,
        ]);
    }

    private function validated(Request $request, ?Service $service): array
    {
        $required = $service ? 'sometimes' : 'required';

        return $request->validate([
            'nama' => [$required, 'string', 'max:255'],
            'kode' => [$required, 'string', 'max:50', Rule::unique('services', 'code')->ignore($service?->id)],
            'kategori' => ['sometimes', 'array'],
            'kategori.*' => ['integer', 'exists:service_categories,id'],
            'jalur' => [$required, Rule::in(array_keys(TicketLabels::MODES))],
            'aktif' => ['sometimes', 'boolean'],
            'waktu_penyelesaian' => ['sometimes', 'nullable', 'string', 'max:100'],
            'biaya' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:99999999'],
            'hasil_digital' => ['sometimes', 'boolean'],
            'deskripsi' => ['sometimes', 'nullable', 'string', 'max:20000'],
            'persyaratan' => ['sometimes', 'nullable', 'string', 'max:20000'],
            'mekanisme' => ['sometimes', 'nullable', 'string', 'max:20000'],
            'produk' => ['sometimes', 'nullable', 'string', 'max:20000'],
            'penanganan_pengaduan' => ['sometimes', 'nullable', 'string', 'max:20000'],
            'untuk' => ['sometimes', 'nullable', 'array'],
            'untuk.*' => [Rule::in(array_keys(FrontDeskService::APPLICANT_TYPES))],
            'disposisi' => ['sometimes', 'array'],
            'disposisi.mode' => ['required_with:disposisi', Rule::in(array_keys(ServiceDisposition::MODES))],
            'disposisi.unit_tujuan' => ['sometimes', 'array'],
            'disposisi.unit_tujuan.*' => [Rule::in(array_keys(ServiceDisposition::RECIPIENTS))],
            'disposisi.anjuran_tanda_tangan' => ['sometimes', 'nullable', Rule::in(array_keys(ServiceDisposition::SIGNATURES))],
            'berkas_persyaratan' => ['sometimes', 'array', 'max:30'],
            'berkas_persyaratan.*.nama' => ['required', 'string', 'max:255', 'distinct'],
            'berkas_persyaratan.*.wajib' => ['sometimes', 'boolean'],
            'berkas_persyaratan.*.keterangan' => ['sometimes', 'nullable', 'string', 'max:500'],
        ], [
            'kode.unique' => 'Kode layanan sudah dipakai layanan lain.',
            'berkas_persyaratan.*.nama.distinct' => 'Nama berkas persyaratan tidak boleh sama.',
        ]);
    }

    private function save(Service $service, array $data): Service
    {
        $html = fn (?string $value) => filled($value) ? (string) str($value)->sanitizeHtml() : null;
        $map = [
            'nama' => ['name', fn ($v) => trim($v)],
            'kode' => ['code', fn ($v) => trim($v)],
            'jalur' => ['mode', fn ($v) => $v],
            'aktif' => ['is_active', fn ($v) => (bool) $v],
            'waktu_penyelesaian' => ['processing_time', fn ($v) => $v],
            'biaya' => ['fee', fn ($v) => $v ?? 0],
            'hasil_digital' => ['is_digital_product', fn ($v) => (bool) $v],
            'deskripsi' => ['description', $html],
            'persyaratan' => ['requirements', $html],
            'mekanisme' => ['mechanism', $html],
            'produk' => ['product', $html],
            'penanganan_pengaduan' => ['complaint_handling', $html],
            'untuk' => ['user_types_allowed', fn ($v) => $v ? array_values(array_unique($v)) : null],
        ];
        foreach ($map as $key => [$column, $cast]) {
            if (array_key_exists($key, $data)) {
                $service->{$column} = $cast($data[$key]);
            }
        }
        $service->save();

        if (array_key_exists('kategori', $data)) {
            $service->categories()->sync($data['kategori']);
        }
        if (isset($data['disposisi'])) {
            ServiceDisposition::apply($service, $data['disposisi']['mode'], $data['disposisi']['unit_tujuan'] ?? [], $data['disposisi']['anjuran_tanda_tangan'] ?? null);
        }
        if (array_key_exists('berkas_persyaratan', $data)) {
            $service->requirements()->delete();
            foreach ($data['berkas_persyaratan'] as $item) {
                $service->requirements()->create([
                    'requirement_name' => trim($item['nama']), 'is_required' => (bool) ($item['wajib'] ?? true), 'description' => $item['keterangan'] ?? null,
                ]);
            }
        }

        return $service;
    }

    private static function summary(Service $service): array
    {
        return [
            'id' => $service->id,
            'kode' => $service->code,
            'nama' => $service->name,
            'slug' => $service->slug,
            'kategori' => $service->categories->map(fn ($c) => ['id' => $c->id, 'nama' => $c->name])->values(),
            'jalur' => $service->mode,
            'jalur_label' => TicketLabels::mode($service->mode),
            'aktif' => (bool) $service->is_active,
            'waktu_penyelesaian' => $service->processing_time,
            'biaya' => (float) $service->fee,
            'untuk' => $service->user_types_allowed ?: [],
        ];
    }
}
