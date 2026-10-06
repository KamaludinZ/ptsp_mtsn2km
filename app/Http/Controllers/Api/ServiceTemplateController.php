<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\TicketActionException;
use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceTemplate;
use App\Services\ServiceTemplateStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Template berkas layanan for the admin: list, upload, edit, replace the file, delete. */
class ServiceTemplateController extends Controller
{
    public function __construct(private ServiceTemplateStorage $storage)
    {
    }

    /** GET /api/layanan-ptsp/{service}/template: every template, active or not, in display order. */
    public function index(Request $request, Service $service): JsonResponse
    {
        $this->authorizeAdmin($request);

        return response()->json([
            'layanan' => ['id' => $service->id, 'nama' => $service->name],
            'data' => $service->templates()->get()->map(fn (ServiceTemplate $template) => self::item($template)),
        ]);
    }

    /** GET /api/layanan-ptsp/{service}/template/{template} */
    public function show(Request $request, Service $service, ServiceTemplate $template): JsonResponse
    {
        $this->authorizeAdmin($request);
        abort_unless($template->service_id === $service->id, 404);

        return response()->json(['data' => self::item($template)]);
    }

    /** PATCH /api/layanan-ptsp/{service}/template/{template} {nama?, wajib?, urutan?, aktif?} */
    public function update(Request $request, Service $service, ServiceTemplate $template): JsonResponse
    {
        $this->authorizeAdmin($request);
        abort_unless($template->service_id === $service->id, 404);

        $data = $request->validate([
            'nama' => ['sometimes', 'required', 'string', 'max:255',
                Rule::unique('service_templates', 'nama')->where('service_id', $service->id)->ignore($template->id)],
            'wajib' => ['sometimes', 'boolean'],
            'urutan' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'aktif' => ['sometimes', 'boolean'],
            'petunjuk' => ['sometimes', 'nullable', 'string', 'max:500'],
        ], ['nama.unique' => 'Layanan ini sudah punya template dengan nama yang sama.']);

        $template->update(array_filter([
            'nama' => isset($data['nama']) ? trim($data['nama']) : null,
            'is_required' => $data['wajib'] ?? null,
            'sort' => $data['urutan'] ?? null,
            'is_active' => $data['aktif'] ?? null,
        ], fn ($value) => $value !== null) + (array_key_exists('petunjuk', $data) ? ['petunjuk' => filled($data['petunjuk']) ? trim($data['petunjuk']) : null] : []));

        activity('audit')->causedBy($request->user())->performedOn($template)->log("Mengubah template {$template->nama}");

        return response()->json(['data' => self::item($template->fresh())]);
    }

    /** POST /api/layanan-ptsp/{service}/template (multipart: berkas, nama, wajib?, urutan?) */
    public function store(Request $request, Service $service): JsonResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'berkas' => ['required', 'file', 'max:' . ServiceTemplateStorage::MAX_KB],
            'nama' => ['required', 'string', 'max:255', Rule::unique('service_templates', 'nama')->where('service_id', $service->id)],
            'wajib' => ['nullable', 'boolean'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'petunjuk' => ['nullable', 'string', 'max:500'],
        ], ['nama.unique' => 'Layanan ini sudah punya template dengan nama yang sama.']);

        try {
            $template = $this->storage->store($service, $data['berkas'], [
                'nama' => $data['nama'], 'is_required' => $data['wajib'] ?? false, 'sort' => $data['urutan'] ?? null,
                'petunjuk' => filled($data['petunjuk'] ?? null) ? trim($data['petunjuk']) : null,
            ]);
        } catch (TicketActionException $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => ['berkas' => [$e->getMessage()]]], 422);
        }

        activity('audit')->causedBy($request->user())->performedOn($template)->log("Mengunggah template {$template->nama} untuk {$service->name}");

        return response()->json(['data' => self::item($template)], 201);
    }

    /** POST /api/layanan-ptsp/{service}/template/{template}/berkas (multipart: berkas) */
    public function replaceFile(Request $request, Service $service, ServiceTemplate $template): JsonResponse
    {
        $this->authorizeAdmin($request);
        abort_unless($template->service_id === $service->id, 404);

        $data = $request->validate(['berkas' => ['required', 'file', 'max:' . ServiceTemplateStorage::MAX_KB]]);

        try {
            $template = $this->storage->replace($template, $data['berkas']);
        } catch (TicketActionException $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => ['berkas' => [$e->getMessage()]]], 422);
        }

        activity('audit')->causedBy($request->user())->performedOn($template)->log("Mengganti berkas template {$template->nama}");

        return response()->json(['data' => self::item($template->fresh())]);
    }

    /** DELETE /api/layanan-ptsp/{service}/template/{template} */
    public function destroy(Request $request, Service $service, ServiceTemplate $template): JsonResponse
    {
        $this->authorizeAdmin($request);
        abort_unless($template->service_id === $service->id, 404);

        $template->delete();
        activity('audit')->causedBy($request->user())->log("Menghapus template {$template->nama} dari {$service->name}");

        return response()->json(['message' => 'Template dihapus.']);
    }

    public static function item(ServiceTemplate $template): array
    {
        return [
            'id' => $template->id,
            'nama' => $template->nama,
            'petunjuk' => $template->petunjuk,
            'nama_berkas' => $template->file_name,
            'jenis' => $template->mime_type,
            'ukuran' => $template->file_size,
            'wajib' => $template->is_required,
            'urutan' => $template->sort,
            'versi' => $template->versi,
            'aktif' => (bool) $template->is_active,
            'diperbarui' => $template->updated_at?->toIso8601String(),
            'tersedia' => $template->isAvailable(),
            'unduh' => $template->isAvailable() ? $template->downloadUrl() : null,
        ];
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasRole('admin'), 403);
    }
}
