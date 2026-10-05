<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ProcessorRoles;
use App\Support\ServiceDisposition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Pemetaan staf ke unit kerja (disposition recipients) as JSON (read: staff; write: admin). */
class UnitStaffController extends Controller
{
    /** GET /api/unit-kerja */
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->isStaff(), 403);

        return response()->json([
            'data' => collect(ProcessorRoles::overview())->map(fn (array $unit) => $this->present($unit))->values(),
        ]);
    }

    /** PUT /api/unit-kerja/{unit} (admin): replace the unit's holders. */
    public function update(Request $request, string $unit): JsonResponse
    {
        abort_unless($request->user()->hasRole('admin'), 403);
        abort_unless(array_key_exists($unit, ServiceDisposition::RECIPIENTS), 404);

        $data = $request->validate([
            'staf' => ['present', 'array'],
            'staf.*' => ['integer', Rule::in(array_keys(ProcessorRoles::eligibleStaff()))],
        ], ['staf.*.in' => 'Staf yang dipilih tidak memiliki akses back office.']);

        ProcessorRoles::assign($unit, $data['staf']);

        activity('audit')->causedBy($request->user())
            ->withProperties(['unit' => $unit, 'staf' => $data['staf']])->log('Mengubah pemegang unit kerja');

        return response()->json($this->present(collect(ProcessorRoles::overview())->firstWhere('name', $unit)));
    }

    private function present(array $unit): array
    {
        return [
            'unit' => $unit['name'],
            'label' => $unit['label'],
            'keterangan' => $unit['description'],
            'jumlah_layanan' => $unit['services'],
            'staf' => $unit['holders']->map(fn ($user) => ['id' => $user->id, 'nama' => $user->name])->values(),
        ];
    }
}
