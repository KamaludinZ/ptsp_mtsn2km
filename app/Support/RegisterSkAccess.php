<?php

namespace App\Support;

use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Hak akses Register Penomoran SK, following the active role (not every
 * role an officer holds): an active role that opens back office and holds
 * the register permission (PERMISSION, granted to every back-office role by
 * default, revocable in Peran & Hak Akses) reads the register, asks for
 * numbers and fills in SK; only the admin role deletes.
 * SuratKeputusanPolicy and the API both ask here.
 */
class RegisterSkAccess
{
    /** The register's own permission, next to the back office area (backoffice.access). */
    public const PERMISSION = 'backoffice.sk.register';

    /** Read the register, ask for a number, correct an SK. */
    public static function canWork(User $user): bool
    {
        return $user->is_active !== false && $user->isStaff()
            && ActiveRoles::can($user, 'backoffice.access') && ActiveRoles::can($user, self::PERMISSION);
    }

    /** Delete an SK: administrators acting as admin. */
    public static function canDelete(User $user): bool
    {
        return $user->is_active !== false && ActiveRoles::hasRole($user, 'admin');
    }

    /**
     * What the user may do in the register right now.
     *
     * @return array{lihat: bool, minta_nomor: bool, ubah: bool, hapus: bool}
     */
    public static function abilities(User $user): array
    {
        $work = self::canWork($user);

        return ['lihat' => $work, 'minta_nomor' => $work, 'ubah' => $work, 'hapus' => self::canDelete($user)];
    }

    /** The register's actions, as the "Akses untuk peran aktif" panel lists them. */
    public const ACTIONS = [
        'lihat' => ['label' => 'Lihat register dan Rincian SK', 'icon' => 'heroicon-o-eye'],
        'minta_nomor' => ['label' => 'Minta nomor SK', 'icon' => 'heroicon-o-hashtag'],
        'ubah' => ['label' => 'Ubah data SK dan unggah scan', 'icon' => 'heroicon-o-pencil-square'],
        'hapus' => ['label' => 'Hapus SK', 'icon' => 'heroicon-o-trash'],
    ];

    /**
     * Each register action for the active role, the roles that may take it,
     * and a role the user also holds to switch to when this one may not.
     *
     * @return list<array{key: string, label: string, icon: string, allowed: bool, roles: list<string>, switch_to: ?array{name: string, label: string}}>
     */
    public static function actionsFor(User $user): array
    {
        $abilities = self::abilities($user);
        $held = ActiveRoles::for($user)->pluck('name');
        $working = self::workingRoles();

        return collect(self::ACTIONS)->map(function (array $action, string $key) use ($abilities, $held, $working) {
            $roles = $key === 'hapus' ? ['admin'] : $working;
            $other = $abilities[$key] ? null : $held->first(fn (string $role) => in_array($role, $roles, true));

            return [
                'key' => $key,
                'label' => $action['label'],
                'icon' => $action['icon'],
                'allowed' => $abilities[$key],
                'roles' => array_map(RoleAccess::roleLabel(...), $roles),
                'switch_to' => $other ? ['name' => $other, 'label' => RoleAccess::roleLabel($other)] : null,
            ];
        })->values()->all();
    }

    /**
     * Staff roles that may work the register with their current permissions.
     *
     * @return array<int, string>
     */
    public static function workingRoles(): array
    {
        return collect(User::STAFF_ROLES)
            ->filter(fn (string $role) => rescue(fn () => Role::findByName($role, 'web')->hasAllPermissions(['backoffice.access', self::PERMISSION]), false, report: false))
            ->values()
            ->all();
    }
}
