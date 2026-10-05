<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Pengaturan Aplikasi: only active administrators read or change it. */
class SettingsAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINTS = [
        ['GET', '/api/pengaturan/profil'], ['PATCH', '/api/pengaturan/profil'],
        ['POST', '/api/pengaturan/profil/logo'], ['DELETE', '/api/pengaturan/profil/logo'],
        ['GET', '/api/pengaturan/umum'], ['PATCH', '/api/pengaturan/umum'],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        AppSetting::set('app_name', 'PTSP');
    }

    public function test_guests_must_sign_in(): void
    {
        foreach (self::ENDPOINTS as [$method, $uri]) {
            $this->json($method, $uri)->assertUnauthorized();
        }
    }

    /** @dataProvider nonAdmins */
    public function test_other_accounts_are_refused(?string $role, bool $active): void
    {
        $user = User::factory()->create(['is_active' => $active]);
        $role && $user->assignRole(Role::findByName($role, 'web'));
        Sanctum::actingAs($user);

        foreach (self::ENDPOINTS as [$method, $uri]) {
            $this->json($method, $uri, ['nama' => 'Diubah'])->assertForbidden();
        }
        $this->assertSame('PTSP', AppSetting::get('app_name'));

        // Their own display preferences are still theirs to change
        $this->patchJson('/api/preferensi-tampilan', ['mode' => 'dark'])->assertOk();
    }

    public static function nonAdmins(): array
    {
        return [
            'front desk' => ['front_desk', true],
            'kepala madrasah' => ['kepala_sekolah', true],
            'pemohon (no role)' => [null, true],
            'deactivated admin' => ['admin', false],
        ];
    }

    public function test_the_gate_holds_without_the_route_middleware(): void
    {
        $admin = User::factory()->create()->assignRole(Role::findByName('admin', 'web'));
        $inactive = User::factory()->create(['is_active' => false])->assignRole(Role::findByName('admin', 'web'));
        $staff = User::factory()->create()->assignRole(Role::findByName('kepala_tu', 'web'));

        $this->assertTrue(Gate::forUser($admin)->allows('kelola-pengaturan'));
        $this->assertFalse(Gate::forUser($inactive)->allows('kelola-pengaturan'));
        $this->assertFalse(Gate::forUser($staff)->allows('kelola-pengaturan'));

        $this->actingAs($admin);
        $this->assertTrue(\App\Filament\Pages\System\Settings::canAccess());
        $this->actingAs($staff);
        $this->assertFalse(\App\Filament\Pages\System\Settings::canAccess());
    }
}
