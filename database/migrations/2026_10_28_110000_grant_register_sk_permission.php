<?php

use App\Support\RoleAccess;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Hak akses Register Penomoran SK as its own permission
 * (backoffice.sk.register), so Peran & Hak Akses can take the SK register
 * away from a back-office role without closing the back office to it.
 * Every role that opens the back office today keeps the register.
 */
return new class extends Migration
{
    public function up(): void
    {
        RoleAccess::sync();
        $permission = Permission::findOrCreate('backoffice.sk.register', 'web');

        Role::query()->where('guard_name', 'web')
            ->whereHas('permissions', fn ($query) => $query->where('name', 'backoffice.access'))
            ->each(fn (Role $role) => $role->givePermissionTo($permission));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Keep the permission: RegisterSkAccess asks for it, removing it would lock the register.
    }
};
