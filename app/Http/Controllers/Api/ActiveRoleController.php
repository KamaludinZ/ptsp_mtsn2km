<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ActiveRoles;
use App\Support\RoleAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Peran aktif petugas: daftar peran staf yang dipegang akun yang masuk dan
 * peran mana yang sedang menjadi konteks kerja. Pemohon tidak punya peran
 * staf, jadi tidak dapat membukanya.
 */
class ActiveRoleController extends Controller
{
    /** GET /api/peran-aktif */
    public function index(Request $request): JsonResponse
    {
        return response()->json($this->overview($request));
    }

    /** PUT /api/peran-aktif {"peran": "front_desk"}: hanya peran staf yang dipegang akun. */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate(['peran' => ['required', 'string', 'max:125']], [
            'peran.required' => 'Pilih peran yang akan diaktifkan.',
        ]);
        $this->ensureStaff($request);

        $switch = ActiveRoles::switchTo($request->user(), $data['peran']);
        $label = RoleAccess::roleLabel($switch['to']);

        return response()->json([
            'message' => $switch['from'] === $switch['to'] ? "Peran {$label} sudah aktif." : "Peran aktif: {$label}.",
            'sebelumnya' => $switch['from'],
        ] + $this->overview($request));
    }

    private function ensureStaff(Request $request): void
    {
        abort_unless($request->user()->isStaff(), 403, 'Hanya akun petugas yang memiliki peran aktif.');
    }

    private function overview(Request $request): array
    {
        $this->ensureStaff($request);
        $user = $request->user();

        $roles = ActiveRoles::for($user);
        $active = $roles->firstWhere('active', true);

        return [
            'aktif' => $active ? $this->present($active) : null,
            'perlu_memilih' => ActiveRoles::needsChoice($user),
            'peran' => $roles->map(fn (array $role) => $this->present($role))->values(),
            // What the active role may do now (TicketPolicy follows the active role).
            'wewenang' => [
                'disposisi' => ActiveRoles::actsAsLeader($user),
                'proses_tiket' => ActiveRoles::can($user, 'backoffice.access'),
                'serah_terima' => ActiveRoles::can($user, 'frontdesk.access'),
                'pengawasan' => ActiveRoles::can($user, 'supervision.access'),
            ],
            'izin' => $active
                ? \Spatie\Permission\Models\Role::findByName($active['name'], 'web')->permissions->pluck('name')->sort()->values()
                : [],
        ];
    }

    private function present(array $role): array
    {
        return [
            'nama' => $role['name'],
            'label' => $role['label'],
            'deskripsi' => $role['description'],
            'area' => $role['areas'],
            'aktif' => $role['active'],
            'terakhir_dipakai' => $role['last_used']?->toIso8601String(),
        ];
    }
}
