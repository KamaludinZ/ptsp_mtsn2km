<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** /api/peran: roles and permissions. */
class RoleApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Sanctum::actingAs(User::factory()->create(['user_type' => 'pegawai'])->assignRole(Role::findByName('admin', 'web')));
    }

    public function test_lists_roles_and_grouped_permissions(): void
    {
        $this->getJson('/api/peran')->assertOk()->assertJsonFragment(['nama' => 'back_office', 'label' => 'Back Office', 'bawaan' => true]);
        $this->getJson('/api/peran/izin')->assertOk()
            ->assertJsonPath('data.Back Office.0.kelompok', 'Back Office')
            ->assertJsonFragment(['nama' => 'backoffice.access', 'membuka_area' => true]);
    }

    public function test_custom_role_lifecycle(): void
    {
        $id = $this->postJson('/api/peran', ['nama' => 'staf_keuangan', 'izin' => ['dashboard.view', 'backoffice.reports.view']])
            ->assertCreated()->assertJsonPath('bawaan', false)->assertJsonPath('izin', ['backoffice.reports.view', 'dashboard.view'])->json('id');

        $this->patchJson("/api/peran/{$id}", ['nama' => 'staf_bendahara', 'izin' => ['dashboard.view']])->assertOk()
            ->assertJsonPath('nama', 'staf_bendahara')->assertJsonPath('izin', ['dashboard.view']);

        $holder = User::factory()->create()->assignRole(Role::findByName('staf_bendahara', 'web'));
        $this->deleteJson("/api/peran/{$id}")->assertStatus(422)->assertJsonPath('message', 'Peran masih dipakai pengguna; pindahkan mereka ke peran lain dulu.');
        $holder->removeRole(Role::findByName('staf_bendahara', 'web'));
        $this->deleteJson("/api/peran/{$id}")->assertOk();
        $this->assertNull(Role::find($id));
    }

    public function test_system_roles_keep_their_name_and_area(): void
    {
        $role = Role::findByName('front_desk', 'web');

        $this->patchJson("/api/peran/{$role->id}", ['nama' => 'loket'])->assertStatus(422)->assertJsonPath('message', 'Nama peran bawaan tidak dapat diubah.');
        $this->patchJson("/api/peran/{$role->id}", ['izin' => ['dashboard.view']])->assertOk()
            ->assertJsonPath('izin', ['dashboard.view', 'frontdesk.access']);
        $this->deleteJson("/api/peran/{$role->id}")->assertStatus(422)->assertJsonPath('message', 'Peran bawaan tidak dapat dihapus.');
    }

    public function test_validation_and_admin_only(): void
    {
        $this->postJson('/api/peran', ['nama' => 'Staf Keuangan!', 'izin' => ['terbang']])->assertStatus(422)->assertJsonValidationErrors(['nama', 'izin.0']);
        $this->postJson('/api/peran', ['nama' => 'admin'])->assertStatus(422)->assertJsonValidationErrors('nama');

        Sanctum::actingAs(User::factory()->create()->assignRole(Role::findByName('back_office', 'web')));
        $this->getJson('/api/peran')->assertForbidden();
    }
}
