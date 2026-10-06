<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Penanda sesi ganti akun sementara di setiap halaman. */
class ImpersonationBannerTest extends TestCase
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

    /** Start a real session as the administrator, at 09.15. */
    private function impersonate(User $target): void
    {
        $this->travelTo(now()->setTime(9, 15));
        $this->actingAs($this->admin())
            ->post(route('ganti-akun.mulai', $target), ['alasan' => 'Meninjau keluhan pengguna'])
            ->assertRedirect();
    }

    public function test_banner_shows_in_the_portal_while_impersonating(): void
    {
        $applicant = User::where('email', 'budi.santoso@email.com')->firstOrFail();
        $this->impersonate($applicant);

        $this->get('/portal')
            ->assertOk()
            ->assertSee('data-impersonation-banner', false)
            ->assertSeeInOrder(['Anda sedang memakai akun', $applicant->name, 'Akun asli: ' . $this->admin()->name, 'sejak 09.15']);
    }

    public function test_banner_shows_in_the_staff_panel_and_public_pages(): void
    {
        $this->impersonate(User::role('front_desk')->firstOrFail());

        $this->get('/cp')->assertOk()->assertSee('data-impersonation-banner', false);
        $this->get('/')->assertOk()->assertSee('data-impersonation-banner', false);
    }

    public function test_way_back_to_the_admin_account_is_offered(): void
    {
        $this->impersonate(User::where('email', 'budi.santoso@email.com')->firstOrFail());

        $this->get('/portal')
            ->assertSee('data-impersonation-leave', false)
            ->assertSee(route('ganti-akun.selesai'), false)
            // Also in the user menu.
            ->assertSeeInOrder(['Kembali ke akun admin', 'Kembali ke akun admin']);
    }

    public function test_no_banner_in_a_normal_session(): void
    {
        $this->actingAs($this->admin())
            ->get('/cp')->assertOk()->assertDontSee('data-impersonation-banner', false)
            ->assertDontSee('Kembali ke akun admin');
    }
}
