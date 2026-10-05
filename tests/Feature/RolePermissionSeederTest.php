<?php

namespace Tests\Feature;

use App\Support\RoleAccess;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Default roles and permissions. */
class RolePermissionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_system_role_exists_with_its_defaults(): void
    {
        $this->seed(RolePermissionSeeder::class);

        foreach (array_keys(RoleAccess::SYSTEM_ROLES) as $name) {
            $role = Role::findByName($name, 'web');
            foreach (RolePermissionSeeder::permissionsFor($name) as $permission) {
                $this->assertTrue($role->hasPermissionTo($permission), "{$name} → {$permission}");
            }
        }

        $this->assertSame(count(RoleAccess::PERMISSION_LABELS), Role::findByName('admin')->permissions()->whereIn('name', array_keys(RoleAccess::PERMISSION_LABELS))->count());
        $this->assertTrue(Role::findByName('front_desk')->hasPermissionTo('frontdesk.visitor.manage'));
        $this->assertFalse(Role::findByName('front_desk')->hasPermissionTo('backoffice.access'));
        $this->assertTrue(Role::findByName('umum')->hasPermissionTo('onlineportal.services.apply'));
        $this->assertFalse(Role::findByName('umum')->hasPermissionTo('dashboard.view'));
    }

    public function test_running_again_keeps_permissions_an_admin_added(): void
    {
        $this->seed(RolePermissionSeeder::class);
        Role::findByName('front_desk')->givePermissionTo('users.view');
        $count = Permission::count();

        $this->seed(RolePermissionSeeder::class);

        $this->assertTrue(Role::findByName('front_desk')->hasPermissionTo('users.view'));
        $this->assertSame($count, Permission::count());
    }

    public function test_area_patterns_expand_to_every_permission_of_the_area(): void
    {
        $this->assertEqualsCanonicalizing(
            array_values(array_filter(array_keys(RoleAccess::PERMISSION_LABELS), fn ($p) => str_starts_with($p, 'frontdesk.'))) + [],
            array_values(array_filter(RolePermissionSeeder::permissionsFor('front_desk'), fn ($p) => str_starts_with($p, 'frontdesk.'))),
        );
    }
}
