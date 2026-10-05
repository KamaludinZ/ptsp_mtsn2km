<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\RoleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** /api/auth: masuk, saya, keluar. */
class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RoleAccess::sync();
    }

    private function bearer(string $token): array
    {
        $this->app['auth']->forgetGuards(); // a fresh request, as a client would make it

        return ['Authorization' => 'Bearer ' . $token, 'Accept' => 'application/json'];
    }

    public function test_officer_signs_in_uses_the_token_and_signs_out(): void
    {
        $officer = User::factory()->create(['email' => 'rina@contoh.id', 'user_type' => 'pegawai'])->assignRole('back_office');

        $response = $this->postJson('/api/auth/masuk', ['email' => 'Rina@Contoh.id', 'password' => 'password', 'perangkat' => 'Android Rina'])
            ->assertOk()
            ->assertJsonPath('jenis_token', 'Bearer')
            ->assertJsonPath('pengguna.peran', ['back_office'])
            ->assertJsonPath('pengguna.peran_label', 'Back Office')
            ->assertJsonPath('pengguna.area', ['back_office']);
        $token = $response->json('token');
        $this->assertSame('Android Rina', $officer->tokens()->sole()->name);
        $this->assertTrue($officer->tokens()->sole()->expires_at->between(now()->addDays(29), now()->addDays(31)));
        $this->assertNotNull($officer->fresh()->last_login_at);

        $this->getJson('/api/auth/saya', $this->bearer($token))->assertOk()->assertJsonPath('email', 'rina@contoh.id');
        $this->getJson('/api/tiket', $this->bearer($token))->assertOk();

        $this->postJson('/api/auth/keluar', [], $this->bearer($token))->assertOk()->assertJsonPath('message', 'Anda telah keluar.');
        $this->assertSame(0, $officer->tokens()->count());
        $this->getJson('/api/auth/saya', $this->bearer($token))->assertUnauthorized();
    }

    public function test_sign_out_everywhere_revokes_every_token(): void
    {
        $user = User::factory()->create();
        $a = $this->postJson('/api/auth/masuk', ['email' => $user->email, 'password' => 'password'])->json('token');
        $this->postJson('/api/auth/masuk', ['email' => $user->email, 'password' => 'password'])->assertOk();

        $this->postJson('/api/auth/keluar-semua', [], $this->bearer($a))->assertOk()->assertJsonPath('token_dicabut', 2);
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_wrong_passwords_lock_the_account_for_a_while(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $i) {
            $this->postJson('/api/auth/masuk', ['email' => $user->email, 'password' => 'salah'])->assertStatus(422);
        }
        $this->postJson('/api/auth/masuk', ['email' => $user->email, 'password' => 'password'])
            ->assertStatus(429)->assertHeader('Retry-After')->assertJsonStructure(['coba_lagi_dalam_detik']);
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_deactivated_and_unverified_accounts_are_refused(): void
    {
        $inactive = User::factory()->create(['is_active' => false]);
        $unverified = User::factory()->unverified()->create(['user_type' => 'umum']);

        $this->postJson('/api/auth/masuk', ['email' => $inactive->email, 'password' => 'password'])
            ->assertForbidden()->assertJsonPath('message', 'Akun Anda dinonaktifkan. Silakan hubungi petugas PTSP.');
        $this->postJson('/api/auth/masuk', ['email' => $unverified->email, 'password' => 'password'])
            ->assertForbidden()->assertJsonPath('message', 'Verifikasi email Anda terlebih dahulu melalui tautan yang kami kirim.');
    }

    public function test_change_password_needs_the_old_one_and_signs_out_other_devices(): void
    {
        $user = User::factory()->create();
        $phone = $this->postJson('/api/auth/masuk', ['email' => $user->email, 'password' => 'password', 'perangkat' => 'HP'])->json('token');
        $this->postJson('/api/auth/masuk', ['email' => $user->email, 'password' => 'password', 'perangkat' => 'Laptop'])->assertOk();

        $this->putJson('/api/auth/kata-sandi', ['kata_sandi_lama' => 'salah', 'kata_sandi_baru' => 'BaruSekali1', 'kata_sandi_baru_confirmation' => 'BaruSekali1'], $this->bearer($phone))
            ->assertStatus(422)->assertJsonValidationErrors(['kata_sandi_lama' => 'Kata sandi saat ini tidak cocok.']);
        $this->putJson('/api/auth/kata-sandi', ['kata_sandi_lama' => 'password', 'kata_sandi_baru' => 'pendek', 'kata_sandi_baru_confirmation' => 'pendek'], $this->bearer($phone))
            ->assertStatus(422)->assertJsonValidationErrors('kata_sandi_baru');

        $this->putJson('/api/auth/kata-sandi', ['kata_sandi_lama' => 'password', 'kata_sandi_baru' => 'BaruSekali1', 'kata_sandi_baru_confirmation' => 'BaruSekali1'], $this->bearer($phone))
            ->assertOk()->assertJsonPath('perangkat_lain_dikeluarkan', 1);

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('BaruSekali1', $user->fresh()->password));
        $this->assertSame(['HP'], $user->tokens()->pluck('name')->all());
    }

    public function test_forgot_and_reset_password_through_the_api(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        $user = User::factory()->create(['email' => 'rina@contoh.id']);
        $user->createToken('lama');

        $this->postJson('/api/auth/lupa-kata-sandi', ['email' => 'Rina@Contoh.id'])->assertOk()
            ->assertJsonPath('message', 'Jika email terdaftar, tautan untuk mengatur ulang kata sandi telah dikirim.');
        $this->postJson('/api/auth/lupa-kata-sandi', ['email' => 'tidak.ada@contoh.id'])->assertOk()
            ->assertJsonPath('message', 'Jika email terdaftar, tautan untuk mengatur ulang kata sandi telah dikirim.');

        $token = null;
        \Illuminate\Support\Facades\Notification::assertSentTo($user, \Illuminate\Auth\Notifications\ResetPassword::class, function ($n) use (&$token) {
            $token = $n->token;

            return true;
        });

        $this->postJson('/api/auth/reset-kata-sandi', ['token' => 'salah', 'email' => 'rina@contoh.id', 'kata_sandi_baru' => 'BaruSekali1', 'kata_sandi_baru_confirmation' => 'BaruSekali1'])
            ->assertStatus(422)->assertJsonPath('message', 'Tautan tidak berlaku atau sudah kedaluwarsa. Minta tautan baru.');
        $this->postJson('/api/auth/reset-kata-sandi', ['token' => $token, 'email' => 'rina@contoh.id', 'kata_sandi_baru' => 'BaruSekali1', 'kata_sandi_baru_confirmation' => 'BaruSekali1'])
            ->assertOk();

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('BaruSekali1', $user->fresh()->password));
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_admin_sends_a_reset_link_without_seeing_a_password(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        $admin = User::factory()->create()->assignRole('admin');
        $user = User::factory()->create();
        \Laravel\Sanctum\Sanctum::actingAs($admin);

        $this->postJson("/api/pengguna/{$user->id}/kirim-reset-kata-sandi")->assertOk()
            ->assertJsonPath('message', "Tautan atur ulang kata sandi dikirim ke {$user->email}.");
        \Illuminate\Support\Facades\Notification::assertSentTo($user, \Illuminate\Auth\Notifications\ResetPassword::class);
    }

    public function test_reset_email_is_in_indonesian_with_a_working_link(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        $user = User::factory()->create(['name' => 'Rina', 'email' => 'rina@contoh.id']);

        $this->postJson('/api/auth/lupa-kata-sandi', ['email' => 'rina@contoh.id'])->assertOk();

        \Illuminate\Support\Facades\Notification::assertSentTo($user, \Illuminate\Auth\Notifications\ResetPassword::class, function ($notification) use ($user) {
            $mail = $notification->toMail($user);
            $this->assertStringStartsWith('Atur ulang kata sandi', $mail->subject);
            $this->assertSame('Halo, Rina!', $mail->greeting);
            $this->assertSame('Atur ulang kata sandi', $mail->actionText);
            $this->assertStringContainsString('/reset-password/' . $notification->token, $mail->actionUrl);
            $this->assertStringContainsString('email=rina%40contoh.id', $mail->actionUrl);
            $this->assertContains('Tautan ini berlaku selama 60 menit.', $mail->outroLines);

            return true;
        });

        // The link opens the Indonesian reset page.
        $this->get(route('password.reset', ['token' => 'x', 'email' => 'rina@contoh.id']))->assertOk()->assertSee('Atur Ulang Password');
    }
}
