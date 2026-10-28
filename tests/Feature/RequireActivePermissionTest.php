<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\RegisterSkAccess;
use App\Support\RoleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/** izin.aktif: route permissions by the active role, on the SK register API and wherever it is used. */
class RequireActivePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function staff(string $acting, string ...$roles): User
    {
        $user = User::factory()->create(['user_type' => 'pegawai']);
        $user->assignRole($roles);
        $user->forceFill(['active_role_id' => Role::findByName($acting, 'web')->id])->save();

        return $user->fresh();
    }

    public function test_a_role_held_but_not_active_does_not_open_the_register(): void
    {
        $user = $this->staff('front_desk', 'front_desk', 'back_office');
        Sanctum::actingAs($user, ['*']);

        $this->getJson('/api/penomoran-sk')->assertForbidden()
            ->assertJsonPath('message', 'Peran aktif Anda (' . RoleAccess::roleLabel('front_desk') . ') tidak berwenang untuk ini (perlu: '
                . RoleAccess::permissionLabel('backoffice.access') . ', serta ' . RoleAccess::permissionLabel(RegisterSkAccess::PERMISSION) . ').'
                . ' Ganti peran aktif ke ' . RoleAccess::roleLabel('back_office') . '.')
            ->assertJsonPath('peran_aktif', 'front_desk')
            ->assertJsonPath('peran_yang_berwenang', ['back_office']);

        // Still told what the active role may do.
        $this->getJson('/api/penomoran-sk/akses')->assertOk()
            ->assertJsonPath('wewenang', ['lihat' => false, 'minta_nomor' => false, 'ubah' => false, 'hapus' => false]);

        $user->forceFill(['active_role_id' => Role::findByName('back_office', 'web')->id])->save();
        Sanctum::actingAs($user->fresh(), ['*']);
        $this->getJson('/api/penomoran-sk')->assertOk();
    }

    public function test_a_role_without_the_register_permission_has_nowhere_to_switch(): void
    {
        Role::findByName('waka_sarpras', 'web')->revokePermissionTo(RegisterSkAccess::PERMISSION);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Sanctum::actingAs($this->staff('waka_sarpras', 'waka_sarpras'), ['*']);

        $this->postJson('/api/penomoran-sk', [])->assertForbidden()
            ->assertJsonPath('peran_yang_berwenang', [])
            ->assertJsonMissingPath('errors');
    }

    public function test_alternatives_roles_and_the_web_page_message(): void
    {
        Route::middleware(['web', 'auth', 'peran.aktif', 'izin.aktif:supervision.access|peran:admin,backoffice.access'])
            ->get('/_uji/izin-aktif', fn () => 'boleh');

        $this->actingAs($this->staff('kepala_tu', 'kepala_tu'))->get('/_uji/izin-aktif')->assertOk()->assertSee('boleh');
        $this->actingAs($this->staff('back_office', 'back_office'))->get('/_uji/izin-aktif')->assertForbidden()
            ->assertSee(RoleAccess::permissionLabel('supervision.access') . ' atau peran ' . RoleAccess::roleLabel('admin'));

        $deactivated = $this->staff('kepala_tu', 'kepala_tu');
        $deactivated->forceFill(['is_active' => false])->save();
        $this->actingAs($deactivated->fresh())->get('/_uji/izin-aktif')->assertForbidden();
    }

    public function test_a_permission_given_to_the_account_itself_counts(): void
    {
        $user = $this->staff('front_desk', 'front_desk');
        $user->givePermissionTo(['backoffice.access', RegisterSkAccess::PERMISSION]);
        Sanctum::actingAs($user->fresh(), ['*']);

        $this->getJson('/api/penomoran-sk/saringan')->assertSuccessful();
    }
}
