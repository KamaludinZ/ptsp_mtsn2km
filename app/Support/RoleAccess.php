<?php

namespace App\Support;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Which staff area each role may open. Permissions belong to roles (not to
 * individual users), so a staff account created from the admin panel gets
 * the right menus and access as soon as it is given its role.
 */
class RoleAccess
{
    public const AREA_PERMISSIONS = ['frontdesk.access', 'backoffice.access', 'supervision.access'];

    public const ROLE_PERMISSIONS = [
        'admin' => ['frontdesk.access', 'backoffice.access', 'supervision.access'],
        'kepala_sekolah' => ['backoffice.access', 'supervision.access'],
        'kepala_tu' => ['frontdesk.access', 'backoffice.access', 'supervision.access'],
        'back_office' => ['backoffice.access'],
        'front_desk' => ['frontdesk.access'],
        'supervisor' => ['supervision.access'],
    ];

    /** Roles that follow up complaints and whistleblowing reports (Modul 10) and read survey reports. */
    public const COMPLAINT_HANDLERS = ['admin', 'supervisor', 'kepala_sekolah', 'kepala_tu'];

    /** Roles that approve service requests (Modul 8) and see the executive dashboard (Modul 13). */
    public const LEADERSHIP = ['admin', 'kepala_sekolah', 'kepala_tu'];

    public static function sync(): void
    {
        foreach (self::AREA_PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (self::ROLE_PERMISSIONS as $role => $permissions) {
            Role::findOrCreate($role, 'web')->givePermissionTo($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
