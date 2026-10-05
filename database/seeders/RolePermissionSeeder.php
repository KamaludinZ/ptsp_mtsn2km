<?php

namespace Database\Seeders;

use App\Support\RoleAccess;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Peran bawaan dan izinnya (safe to run again, also in production): every
 * system role exists with its default permissions. Permissions an admin
 * granted later are kept; nothing is revoked.
 */
class RolePermissionSeeder extends Seeder
{
    /** role => permission patterns ("backoffice.*" = every permission of that area). */
    public const DEFAULTS = [
        'admin' => ['*'],
        'kepala_sekolah' => ['dashboard.view', 'backoffice.access', 'backoffice.tickets.view', 'backoffice.reports.view', 'supervision.*'],
        'kepala_tu' => ['dashboard.view', 'frontdesk.*', 'backoffice.*', 'supervision.*'],
        'supervisor' => ['dashboard.view', 'supervision.*'],
        'back_office' => ['dashboard.view', 'backoffice.*'],
        'front_desk' => ['dashboard.view', 'frontdesk.*'],
        'waka_humas' => ['dashboard.view', 'backoffice.access', 'backoffice.tickets.view', 'backoffice.tickets.process'],
        'waka_kesiswaan' => ['dashboard.view', 'backoffice.access', 'backoffice.tickets.view', 'backoffice.tickets.process'],
        'waka_kurikulum' => ['dashboard.view', 'backoffice.access', 'backoffice.tickets.view', 'backoffice.tickets.process'],
        'waka_sarpras' => ['dashboard.view', 'backoffice.access', 'backoffice.tickets.view', 'backoffice.tickets.process'],
        'tata_usaha' => ['dashboard.view', 'backoffice.*'],
        'penjamin_mutu' => ['dashboard.view', 'backoffice.access', 'backoffice.tickets.view', 'backoffice.reports.view', 'supervision.performance.view', 'supervision.surveys.view'],
        // Applicants: the portal.
        'guru' => ['onlineportal.*'],
        'pegawai' => ['onlineportal.*'],
        'siswa' => ['onlineportal.*'],
        'walimurid' => ['onlineportal.*'],
        'alumni' => ['onlineportal.*'],
        'instansi' => ['onlineportal.*'],
        'umum' => ['onlineportal.*'],
    ];

    public function run(): void
    {
        RoleAccess::sync(); // every labelled permission + the area permissions roles need

        foreach (array_keys(RoleAccess::SYSTEM_ROLES) as $name) {
            $role = Role::findOrCreate($name, 'web');
            $role->givePermissionTo(self::permissionsFor($name));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** @return array<int, string> the permission names a role gets by default */
    public static function permissionsFor(string $role): array
    {
        $all = array_keys(RoleAccess::PERMISSION_LABELS);

        return collect(self::DEFAULTS[$role] ?? [])
            ->flatMap(fn (string $pattern) => $pattern === '*' ? $all
                : (str_ends_with($pattern, '.*') ? array_filter($all, fn ($p) => str_starts_with($p, substr($pattern, 0, -1))) : [$pattern]))
            ->merge(RoleAccess::ROLE_PERMISSIONS[$role] ?? [])
            ->unique()->values()->all();
    }
}
