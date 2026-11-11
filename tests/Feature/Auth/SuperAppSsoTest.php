<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SuperAppSsoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'superapp_sso.url' => 'https://superapp.test/api/sso',
            'superapp_sso.kunci' => 'sso_rahasia',
            'superapp_sso.portal' => null,
        ]);
    }

    private function fakeTukar(array $pengguna, int $status = 200): void
    {
        Http::fake([
            'superapp.test/api/sso/tukar' => Http::response(
                $status === 200
                    ? ['aplikasi' => 'ptsp', 'pengguna' => $pengguna, 'masuk_pada' => now()->toIso8601String()]
                    : ['detail' => ['kode' => $pengguna['kode'] ?? 'tiket_tidak_valid', 'pesan' => 'x']],
                $status,
            ),
        ]);
    }

    public function test_login_page_shows_the_super_app_button_when_configured(): void
    {
        $this->get('/login')->assertOk()
            ->assertSee('Masuk lewat Super App')
            ->assertSee('href="https://superapp.test/"', false)
            // SSO hanya alternatif: form e-mail + password bawaan tetap ada
            ->assertSee('id="loginForm"', false)
            ->assertSee('name="password"', false);
    }

    public function test_email_and_password_login_still_works_when_sso_is_configured(): void
    {
        $user = User::factory()->create();
        Http::fake();

        $this->withSession(['captcha_value' => '123456'])->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'captcha' => '123456',
        ])->assertRedirect('/portal');

        $this->assertAuthenticatedAs($user);
        Http::assertNothingSent();
    }

    public function test_login_page_hides_the_button_until_configured(): void
    {
        config(['superapp_sso.kunci' => null]);

        $this->get('/login')->assertOk()->assertDontSee('Masuk lewat Super App');
    }

    public function test_a_guru_signs_in_with_a_ticket_matched_by_email(): void
    {
        $user = User::factory()->create(['email' => 'budi@madrasah.sch.id', 'user_type' => 'guru']);
        $this->fakeTukar(['email' => 'Budi@Madrasah.sch.id', 'jenis' => 'guru', 'username' => 'budi']);

        $this->get('/sso/masuk?tiket=abc123')->assertRedirect('/portal');

        $this->assertAuthenticatedAs($user);
        Http::assertSent(fn (Request $r) => $r->url() === 'https://superapp.test/api/sso/tukar'
            && $r->hasHeader('Authorization', 'Bearer sso_rahasia')
            && $r['tiket'] === 'abc123');
        $this->assertDatabaseHas('login_attempts', ['email' => 'budi@madrasah.sch.id', 'successful' => true]);
    }

    public function test_staff_land_in_the_control_panel(): void
    {
        \App\Support\RoleAccess::sync();
        $user = User::factory()->create(['email' => 'tu@madrasah.sch.id', 'user_type' => 'pegawai']);
        $user->assignRole('front_desk');
        $this->fakeTukar(['email' => 'tu@madrasah.sch.id', 'jenis' => 'pegawai']);

        $this->get('/sso/masuk?tiket=abc')->assertRedirect('/cp');
        $this->assertAuthenticatedAs($user);
    }

    public function test_missing_ticket_is_rejected_without_calling_super_app(): void
    {
        Http::fake();

        $this->get('/sso/masuk')->assertRedirect('/login')->assertSessionHasErrors('sso');
        Http::assertNothingSent();
        $this->assertGuest();
    }

    public function test_rejected_ticket_shows_a_friendly_message(): void
    {
        $this->fakeTukar(['kode' => 'tiket_kedaluwarsa'], 400);

        $this->get('/sso/masuk?tiket=lama')->assertRedirect('/login')
            ->assertSessionHasErrors(['sso' => 'Tiket login sudah kedaluwarsa. Buka lagi PTSP dari dashboard Super App.']);
        $this->assertGuest();
    }

    public function test_unknown_email_is_not_signed_in(): void
    {
        $this->fakeTukar(['email' => 'belum@madrasah.sch.id', 'jenis' => 'siswa']);

        $this->get('/sso/masuk?tiket=abc')->assertRedirect('/login')->assertSessionHasErrors('sso');
        $this->assertGuest();
    }

    public function test_jenis_outside_siswa_guru_pegawai_is_refused(): void
    {
        User::factory()->create(['email' => 'alumni@madrasah.sch.id', 'user_type' => 'alumni']);
        $this->fakeTukar(['email' => 'alumni@madrasah.sch.id', 'jenis' => 'alumni']);

        $this->get('/sso/masuk?tiket=abc')->assertRedirect('/login')->assertSessionHasErrors('sso');
        $this->assertGuest();
    }

    public function test_deactivated_account_is_refused(): void
    {
        User::factory()->create(['email' => 'siswa@madrasah.sch.id', 'user_type' => 'siswa', 'is_active' => false]);
        $this->fakeTukar(['email' => 'siswa@madrasah.sch.id', 'jenis' => 'siswa']);

        $this->get('/sso/masuk?tiket=abc')->assertRedirect('/login')->assertSessionHasErrors('sso');
        $this->assertGuest();
    }

    public function test_not_configured_is_refused(): void
    {
        config(['superapp_sso.kunci' => null]);
        Http::fake();

        $this->get('/sso/masuk?tiket=abc')->assertRedirect('/login')->assertSessionHasErrors('sso');
        Http::assertNothingSent();
    }
}
