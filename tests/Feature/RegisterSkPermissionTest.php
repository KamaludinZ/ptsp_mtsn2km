<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource;
use App\Models\User;
use App\Support\ActiveRoles;
use App\Support\RegisterSkAccess;
use App\Support\RoleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/** backoffice.sk.register: the SK register as its own permission in Peran & Hak Akses. */
class RegisterSkPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function staff(string $role): User
    {
        $user = User::factory()->create(['user_type' => 'pegawai']);
        $user->assignRole($role);
        $this->actingAs($user = $user->fresh());
        request()->setUserResolver(fn () => $user);
        request()->attributes->set(ActiveRoles::REQUEST_ATTRIBUTE, $role);

        return $user;
    }

    private function forget(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_every_back_office_role_holds_the_register_by_default(): void
    {
        $this->assertSame('Back Office — Register Penomoran SK (lihat, minta nomor, ubah data dan scan)', RoleAccess::PERMISSION_LABELS[RegisterSkAccess::PERMISSION]);

        $backOffice = Role::query()->whereHas('permissions', fn ($q) => $q->where('name', 'backoffice.access'))->pluck('name')->sort()->values()->all();
        $register = Role::query()->whereHas('permissions', fn ($q) => $q->where('name', RegisterSkAccess::PERMISSION))->pluck('name')->sort()->values()->all();

        $this->assertNotEmpty($backOffice);
        $this->assertSame($backOffice, $register);
        $this->assertNotContains('front_desk', $register);
    }

    public function test_taking_the_permission_away_closes_the_register_but_not_the_back_office(): void
    {
        Role::findByName('waka_sarpras', 'web')->revokePermissionTo(RegisterSkAccess::PERMISSION);
        $this->forget();
        $user = $this->staff('waka_sarpras');

        $this->assertTrue(ActiveRoles::can($user, 'backoffice.access'));
        $this->assertSame(['lihat' => false, 'minta_nomor' => false, 'ubah' => false, 'hapus' => false], RegisterSkAccess::abilities($user));
        $this->assertFalse(SuratKeputusanResource::canViewAny());
        $this->assertNotContains('waka_sarpras', RegisterSkAccess::workingRoles());
        $this->assertContains('waka_kurikulum', RegisterSkAccess::workingRoles());
    }

    public function test_the_register_permission_alone_does_not_open_it(): void
    {
        $role = Role::findByName('front_desk', 'web');
        $role->givePermissionTo(RegisterSkAccess::PERMISSION);
        $this->forget();

        $this->assertFalse(RegisterSkAccess::canWork($this->staff('front_desk')));

        $role->givePermissionTo('backoffice.access');
        $this->forget();
        $this->assertTrue(RegisterSkAccess::canWork($this->staff('front_desk')));
    }

    public function test_the_migration_keeps_today_s_access_for_every_back_office_role(): void
    {
        $custom = Role::create(['name' => 'koordinator_arsip', 'guard_name' => 'web']);
        $custom->givePermissionTo('backoffice.access');
        Role::findByName('back_office', 'web')->revokePermissionTo(RegisterSkAccess::PERMISSION);

        (require database_path('migrations/2026_10_28_110000_grant_register_sk_permission.php'))->up();

        $this->assertTrue($custom->fresh()->hasPermissionTo(RegisterSkAccess::PERMISSION));
        $this->assertTrue(Role::findByName('back_office', 'web')->hasPermissionTo(RegisterSkAccess::PERMISSION));
        $this->assertFalse(Role::findByName('front_desk', 'web')->hasPermissionTo(RegisterSkAccess::PERMISSION));
    }
}
