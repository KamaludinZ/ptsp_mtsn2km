<?php

namespace Tests\Feature;

use App\Filament\Pages\Dashboard;
use App\Models\User;
use App\Support\ActiveRoleToast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** POST /peran-aktif/ganti: ganti peran cepat dari header /cp. */
class ActiveRoleSwitchTest extends TestCase
{
    use RefreshDatabase;

    private function staff(string ...$roles): User
    {
        $user = User::factory()->create();
        foreach ($roles as $role) {
            $user->assignRole(Role::findOrCreate($role, 'web'));
        }

        return $user;
    }

    private function toast(): array
    {
        return collect(session('filament.notifications'))->last();
    }

    public function test_requires_sign_in_and_a_staff_account(): void
    {
        $this->post('/peran-aktif/ganti', ['peran' => 'front_desk'])->assertRedirect('/login');

        $this->actingAs($this->staff('umum'))
            ->post('/peran-aktif/ganti', ['peran' => 'umum'])->assertForbidden();
    }

    public function test_switches_and_returns_to_the_same_page(): void
    {
        $user = $this->staff('kepala_tu', 'front_desk');

        $this->actingAs($user)
            ->post('/peran-aktif/ganti', ['peran' => 'front_desk', 'kembali' => '/cp/notifikasi?halaman=2'])
            ->assertRedirect(url('/cp/notifikasi?halaman=2'));

        $this->assertSame('front_desk', $user->fresh()->activeRole->name);
        // First choice: nothing to go back to.
        $this->assertSame('Peran aktif: Front Desk', $this->toast()['title']);
        $this->assertSame([], $this->toast()['actions']);

        $this->post('/peran-aktif/ganti', ['peran' => 'kepala_tu', 'kembali' => url('/cp/notifikasi')])
            ->assertRedirect(url('/cp/notifikasi'));

        $this->assertSame('kepala_tu', $user->fresh()->activeRole->name);
        $this->assertStringContainsString('Berpindah dari Front Desk', $this->toast()['body']);
        $this->assertSame(['role' => 'front_desk'], $this->toast()['actions'][0]['eventData']);
        $this->assertSame(ActiveRoleToast::UNDO_EVENT, $this->toast()['actions'][0]['event']);
    }

    public function test_switching_to_the_active_role_just_says_so(): void
    {
        $user = $this->staff('kepala_tu', 'front_desk');
        $this->actingAs($user)->post('/peran-aktif/ganti', ['peran' => 'front_desk']);

        $this->post('/peran-aktif/ganti', ['peran' => 'front_desk'])->assertRedirect(Dashboard::getUrl());
        $this->assertSame('Anda sudah memakai peran Front Desk', $this->toast()['title']);
    }

    public function test_a_role_not_held_is_refused_and_nothing_changes(): void
    {
        $user = $this->staff('front_desk', 'back_office');
        Role::findOrCreate('admin', 'web');

        $this->actingAs($user)
            ->post('/peran-aktif/ganti', ['peran' => 'admin', 'kembali' => '/cp/notifikasi'])
            ->assertRedirect(url('/cp/notifikasi'));

        $this->assertSame('Peran tidak tersedia untuk akun ini', $this->toast()['title']);
        $this->assertNull($user->fresh()->active_role_id);

        $this->postJson('/peran-aktif/ganti', ['peran' => 'admin'])->assertUnprocessable()
            ->assertJsonValidationErrors(['peran' => 'Peran ini tidak Anda pegang.']);
    }

    public function test_json_callers_get_the_switch_result(): void
    {
        $this->actingAs($this->staff('kepala_tu', 'front_desk'))
            ->postJson('/peran-aktif/ganti', ['peran' => 'kepala_tu', 'kembali' => '/cp'])
            ->assertOk()
            ->assertExactJson(['sebelumnya' => null, 'aktif' => 'kepala_tu', 'kembali' => url('/cp')]);
    }

    public function test_never_returns_to_another_site(): void
    {
        $this->actingAs($this->staff('kepala_tu', 'front_desk'));

        foreach (['https://evil.example/cp', '//evil.example/cp', '/\\evil.example', 'javascript:alert(1)', "/cp\r\nLocation: x", 'http://localhost.evil.example/'] as $url) {
            $this->post('/peran-aktif/ganti', ['peran' => 'front_desk', 'kembali' => $url])
                ->assertRedirect(Dashboard::getUrl());
        }
    }
}
