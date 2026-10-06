<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Support\ServiceDisposition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Pengaturan disposisi per layanan as JSON (read: staff; write: admin). */
class ServiceDispositionController extends Controller
{
    /** GET /api/pengaturan-disposisi */
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->isStaff(), 403);

        return response()->json([
            'data' => Service::orderBy('name')->get()->map(fn (Service $service) => $this->present($service))->values(),
        ]);
    }

    /** GET /api/pengaturan-disposisi/{service:slug} */
    public function show(Request $request, Service $service): JsonResponse
    {
        abort_unless($request->user()->isStaff(), 403);

        return response()->json($this->present($service));
    }

    /** PUT /api/pengaturan-disposisi/{service:slug} (admin) */
    public function update(Request $request, Service $service): JsonResponse
    {
        abort_unless(\App\Support\ActiveRoles::hasRole($request->user(), 'admin'), 403, 'Pengaturan disposisi diubah dari peran aktif Administrator.');

        $data = $request->validate([
            'mode' => ['required', Rule::in(array_keys(ServiceDisposition::MODES))],
            'penerima' => ['nullable', 'array'],
            'penerima.*' => [Rule::in(array_keys(ServiceDisposition::RECIPIENTS))],
            'anjuran_tanda_tangan' => ['nullable', Rule::in(array_keys(ServiceDisposition::SIGNATURES))],
        ]);

        ServiceDisposition::apply($service, $data['mode'], $data['penerima'] ?? [], $data['anjuran_tanda_tangan'] ?? null);

        activity('audit')->causedBy($request->user())->performedOn($service)
            ->withProperties($data)->log('Mengubah pengaturan disposisi');

        return response()->json($this->present($service->fresh()));
    }

    private function present(Service $service): array
    {
        $mode = $service->disposition_mode;

        return [
            'layanan' => $service->name,
            'slug' => $service->slug,
            'aktif' => (bool) $service->is_active,
            'mode' => $mode,
            'mode_label' => ServiceDisposition::mode($mode),
            'aturan_bawaan' => ServiceDisposition::usesDefault($service),
            'penerima' => $service->disposition_roles ?? [],
            'penerima_label' => ServiceDisposition::recipients($service->disposition_roles),
            'anjuran_tanda_tangan' => $service->signature_recommendation,
            'anjuran_tanda_tangan_label' => ServiceDisposition::signature($service->signature_recommendation),
        ];
    }
}
