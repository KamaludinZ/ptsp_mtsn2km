<?php

namespace App\Http\Controllers\Api;

use App\Filament\Resources\RoleResource;
use App\Http\Controllers\Controller;
use App\Support\RoleAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Peran dan izin (admin), with the role page's rules: system roles keep
 * their name and the area permission they need; a custom role can be
 * deleted only when nobody holds it.
 */
class RoleController extends Controller
{
    /** GET /api/peran */
    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $roles = Role::where('guard_name', 'web')->with('permissions:id,name')->orderBy('name')->get();

        return response()->json(['data' => $roles->map(fn (Role $role) => self::item($role))]);
    }

    /** GET /api/peran/izin: every permission, grouped by area, with its description. */
    public function permissions(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        return response()->json([
            'data' => Permission::where('guard_name', 'web')->orderBy('name')->get(['name'])
                ->map(fn (Permission $p) => [
                    'nama' => $p->name,
                    'kelompok' => trim(explode('—', RoleAccess::permissionLabel($p->name))[0]),
                    'keterangan' => RoleAccess::permissionLabel($p->name),
                    'membuka_area' => in_array($p->name, RoleAccess::AREA_PERMISSIONS, true),
                ])
                ->groupBy('kelompok'),
        ]);
    }

    /** GET /api/peran/{role} */
    public function show(Request $request, Role $role): JsonResponse
    {
        $this->authorizeAdmin($request);

        return response()->json(self::item($role->load('permissions:id,name')));
    }

    /** POST /api/peran {nama, izin?[]} — a custom role. */
    public function store(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:50', 'regex:/^[a-z][a-z0-9_]*$/', Rule::unique('roles', 'name')->where('guard_name', 'web')],
            'izin' => ['sometimes', 'array'],
            'izin.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'web')],
        ], ['nama.regex' => 'Nama peran memakai huruf kecil, angka, dan garis bawah, mis. staf_keuangan.']);

        $role = DB::transaction(function () use ($data) {
            $role = Role::create(['name' => $data['nama'], 'guard_name' => 'web']);
            $role->syncPermissions($data['izin'] ?? []);

            return $role;
        });
        RoleResource::flushPermissionCache();
        activity('audit')->causedBy($request->user())->performedOn($role)->log('Menambah peran ' . $role->name);

        return response()->json(self::item($role->load('permissions:id,name')), 201);
    }

    /** PATCH /api/peran/{role} {nama?, izin?[]} */
    public function update(Request $request, Role $role): JsonResponse
    {
        $this->authorizeAdmin($request);
        $data = $request->validate([
            'nama' => ['sometimes', 'string', 'max:50', 'regex:/^[a-z][a-z0-9_]*$/', Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role->id)],
            'izin' => ['sometimes', 'array'],
            'izin.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'web')],
        ]);

        if (isset($data['nama']) && $data['nama'] !== $role->name && RoleAccess::isSystemRole($role->name)) {
            return response()->json(['message' => 'Nama peran bawaan tidak dapat diubah.'], 422);
        }

        DB::transaction(function () use ($role, $data) {
            if (isset($data['nama'])) {
                $role->update(['name' => $data['nama']]);
            }
            if (array_key_exists('izin', $data)) {
                // A system role always keeps the area it needs.
                $role->syncPermissions(array_unique([...$data['izin'], ...RoleAccess::requiredPermissions($role->name)]));
            }
        });
        RoleResource::flushPermissionCache();
        activity('audit')->causedBy($request->user())->performedOn($role)->log('Mengubah peran ' . $role->name);

        return response()->json(self::item($role->fresh()->load('permissions:id,name')));
    }

    /** DELETE /api/peran/{role} */
    public function destroy(Request $request, Role $role): JsonResponse
    {
        $this->authorizeAdmin($request);

        if (RoleAccess::isSystemRole($role->name) || self::holders($role) > 0) {
            return response()->json(['message' => RoleAccess::isSystemRole($role->name)
                ? 'Peran bawaan tidak dapat dihapus.'
                : 'Peran masih dipakai pengguna; pindahkan mereka ke peran lain dulu.'], 422);
        }

        $role->delete();
        RoleResource::flushPermissionCache();
        activity('audit')->causedBy($request->user())->log('Menghapus peran ' . $role->name);

        return response()->json(['message' => 'Peran dihapus.']);
    }

    private static function item(Role $role): array
    {
        return [
            'id' => $role->id,
            'nama' => $role->name,
            'label' => RoleAccess::roleLabel($role->name),
            'bawaan' => RoleAccess::isSystemRole($role->name),
            'jumlah_pengguna' => self::holders($role),
            'izin' => $role->permissions->pluck('name')->sort()->values(),
            'izin_wajib' => RoleAccess::requiredPermissions($role->name),
            'dapat_dihapus' => ! RoleAccess::isSystemRole($role->name) && self::holders($role) === 0,
        ];
    }

    /** Users holding the role (read directly: Role::users() follows the request's guard, which is sanctum here). */
    private static function holders(Role $role): int
    {
        return \Illuminate\Support\Facades\DB::table('model_has_roles')->where('role_id', $role->id)->count();
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasRole('admin'), 403);
    }
}
