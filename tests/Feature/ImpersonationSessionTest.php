<?php

namespace Tests\Feature;

use App\Models\ImpersonationSession;
use App\Models\User;
use App\Support\Impersonation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Sesi ganti akun di backend: diakhiri, kedaluwarsa, dan dijaga tiap request. */
class ImpersonationSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function admin(): User
    {
        return User::role('admin')->firstOrFail();
    }

    private function applicant(): User
    {
        return User::where('email', 'budi.santoso@email.com')->firstOrFail();
    }

    private function impersonate(): void
    {
        $this->actingAs($this->admin())
            ->post(route('ganti-akun.mulai', $this->applicant()), ['alasan' => 'Meninjau keluhan unggah berkas'])
            ->assertRedirect('/portal');
        $this->assertAuthenticatedAs($this->applicant());
    }

    public function test_back_to_the_admin_account_closes_the_log(): void
    {
        $this->impersonate();

        $this->post(route('ganti-akun.selesai'))->assertRedirect('/cp');

        $this->assertAuthenticatedAs($this->admin());
        $this->assertNull(session(Impersonation::SESSION_KEY));
        $log = ImpersonationSession::sole();
        $this->assertFalse($log->isOpen());
        $this->assertSame('selesai', $log->end_reason);

        // The admin's own session carries on normally.
        $this->get('/cp')->assertOk()->assertDontSee('data-impersonation-banner', false);
    }

    public function test_a_session_past_its_limit_ends_by_itself(): void
    {
        config(['impersonation.max_minutes' => 30]);
        $this->impersonate();

        $this->travel(31)->minutes();
        $this->get('/portal')->assertRedirect('/cp');

        $this->assertAuthenticatedAs($this->admin());
        $this->assertSame('kedaluwarsa', ImpersonationSession::sole()->end_reason);
    }

    public function test_an_admin_who_lost_access_is_signed_out(): void
    {
        $this->impersonate();
        $this->admin()->update(['is_active' => false]);

        $this->get('/portal')->assertRedirect('/login');

        $this->assertGuest();
        $this->assertSame('kedaluwarsa', ImpersonationSession::sole()->end_reason);
    }

    public function test_signing_out_of_the_borrowed_account_closes_the_log(): void
    {
        $this->impersonate();

        $this->post('/logout');

        $this->assertGuest();
        $this->assertSame('keluar', ImpersonationSession::sole()->end_reason);
    }

    public function test_a_session_whose_log_was_closed_elsewhere_ends(): void
    {
        $this->impersonate();
        ImpersonationSession::sole()->update(['ended_at' => now(), 'end_reason' => 'kedaluwarsa']);

        $this->get('/portal')->assertRedirect('/cp');
        $this->assertAuthenticatedAs($this->admin());
    }

    public function test_json_callers_get_the_result(): void
    {
        $this->impersonate();

        $this->postJson(route('ganti-akun.selesai'))->assertOk()
            ->assertExactJson(['diakhiri' => true, 'akun_dipakai' => $this->applicant()->name, 'akun_aktif' => $this->admin()->name]);

        $this->postJson(route('ganti-akun.selesai'))->assertStatus(409)->assertJsonPath('diakhiri', false);
    }

    public function test_nothing_to_end_without_a_session(): void
    {
        $this->actingAs($this->admin())->post(route('ganti-akun.selesai'))->assertRedirect();

        $this->assertAuthenticatedAs($this->admin());
        $this->assertSame('Tidak ada sesi ganti akun', collect(session('filament.notifications'))->last()['title']);
    }
}
