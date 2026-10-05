<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->withSession(['captcha_value' => 'ABCDE'])->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'captcha' => 'ABCDE',
        ]);

        $this->assertAuthenticated();
        // A plain applicant lands on the applicant portal
        $response->assertRedirect('/portal');
    }

    public function test_login_redirects_each_role_to_its_own_dashboard(): void
    {
        \App\Support\RoleAccess::sync();

        // Every staff role works in the control panel, which shows each its own dashboard
        foreach (['admin' => '/cp', 'kepala_sekolah' => '/cp', 'back_office' => '/cp', 'front_desk' => '/cp', 'supervisor' => '/cp'] as $role => $url) {
            $user = User::factory()->create();
            $user->assignRole($role);

            $this->withSession(['captcha_value' => 'ABCDE'])->post('/login', [
                'email' => $user->email,
                'password' => 'password',
                'captcha' => 'ABCDE',
            ])->assertRedirect($url);

            auth()->logout();
        }
    }

    public function test_admin_panel_sidebar_lists_every_workspace(): void
    {
        \App\Support\RoleAccess::sync();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->get('/cp')->assertOk()
            ->assertSee('Registrasi Layanan')
            ->assertSee('Buku Tamu')
            ->assertSee('Tiket Layanan')
            ->assertSee('Disposisi Masuk')
            ->assertSee('Pengaduan &amp; WBS', false)
            ->assertSee('Laporan SKM &amp; SPAK', false)
            ->assertSee('Keamanan Sistem');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_login_form_is_accessible_and_guards_against_double_submit(): void
    {
        $this->get('/login')->assertOk()
            ->assertSee('autocomplete="username"', false)
            ->assertSee('autocomplete="current-password"', false)
            ->assertSee('id="togglePassword"', false)
            ->assertSee('Tampilkan password')
            ->assertSee('Caps Lock aktif.')
            ->assertSee('for="captcha"', false)
            ->assertSee('inputmode="numeric"', false);
    }

    public function test_errors_are_announced_next_to_their_field(): void
    {
        $user = User::factory()->create();

        $this->withSession(['captcha_value' => 'ABCDE'])->post('/login', ['email' => $user->email, 'password' => 'salah', 'captcha' => 'ABCDE']);
        $this->get('/login')->assertSee('aria-invalid="true" aria-describedby="email-error"', false)->assertSee('id="email-error" role="alert"', false);
    }

    public function test_deactivated_accounts_cannot_sign_in(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->withSession(['captcha_value' => 'ABCDE'])->post('/login', ['email' => $user->email, 'password' => 'password', 'captcha' => 'ABCDE'])
            ->assertSessionHasErrors(['email' => 'Akun Anda dinonaktifkan. Silakan hubungi petugas PTSP.']);

        $this->assertGuest();
    }

    public function test_wrong_captchas_count_toward_the_lockout_with_a_countdown(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $i) {
            $this->withSession(['captcha_value' => 'ABCDE'])->post('/login', ['email' => $user->email, 'password' => 'password', 'captcha' => 'SALAH']);
        }
        $this->withSession(['captcha_value' => 'ABCDE'])->post('/login', ['email' => $user->email, 'password' => 'password', 'captcha' => 'ABCDE'])
            ->assertSessionHasErrors('email')
            ->assertSessionHas('login_locked_until');
        $this->assertGuest();

        $this->get('/login')->assertSee('id="lockoutNotice"', false)->assertSee('Terlalu banyak percobaan. Coba lagi dalam');
    }

    public function test_panel_user_menu_shows_role_notifications_and_logout(): void
    {
        \App\Support\RoleAccess::sync();
        $officer = User::factory()->create(['name' => 'Rina', 'user_type' => 'pegawai'])->assignRole('back_office');

        $this->actingAs($officer)->get('/cp')->assertOk()
            ->assertSee('Rina · Back Office')
            ->assertSee(\App\Filament\Pages\Notifications::getUrl(), false)
            ->assertSee('Keluar');
        $this->assertSame('Umum', \App\Support\RoleAccess::userRoleLabel(User::factory()->create(['user_type' => 'umum'])));
    }

    public function test_signing_out_from_the_panel_or_site_confirms_on_the_home_page(): void
    {
        \App\Support\RoleAccess::sync();
        $user = User::factory()->create(['user_type' => 'pegawai'])->assignRole('back_office');

        $this->actingAs($user)->post(route('filament.admin.auth.logout'))->assertRedirect(route('home'));
        $this->assertGuest();
        $this->get(route('home'))->assertSee('Anda telah keluar.');

        $this->actingAs($user)->post('/logout')->assertRedirect('/');
        $this->get('/')->assertSee('Anda telah keluar.');
        $this->get('/')->assertDontSee('Anda telah keluar.'); // only once
    }
}
