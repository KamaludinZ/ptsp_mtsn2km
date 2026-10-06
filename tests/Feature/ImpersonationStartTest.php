<?php

namespace Tests\Feature;

use App\Models\ImpersonationSession;
use App\Models\User;
use App\Support\ActiveRoles;
use App\Support\Impersonation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** POST /ganti-akun/{user}: administrator mulai memakai akun pengguna lain. */
class ImpersonationStartTest extends TestCase
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

    public function test_admin_switches_to_the_account_and_it_is_logged(): void
    {
        $admin = $this->admin();
        $applicant = $this->applicant();
        $lastLogin = $applicant->last_login_at;

        $this->actingAs($admin)
            ->post(route('ganti-akun.mulai', $applicant), ['alasan' => 'Meninjau keluhan unggah berkas'])
            ->assertRedirect('/portal');

        $this->assertAuthenticatedAs($applicant);
        $log = ImpersonationSession::sole();
        $this->assertSame([$admin->id, $admin->name, $applicant->id, $applicant->name, 'Meninjau keluhan unggah berkas'],
            [$log->admin_id, $log->admin_name, $log->target_id, $log->target_name, $log->reason]);
        $this->assertTrue($log->isOpen());
        $this->assertNotNull($log->ip_address);
        $this->assertSame(['admin_id' => $admin->id, 'log_id' => $log->id], array_intersect_key(session(Impersonation::SESSION_KEY), ['admin_id' => 1, 'log_id' => 1]));

        // The target's own sign-in record is untouched.
        $this->assertEquals($lastLogin, $applicant->fresh()->last_login_at);

        // The next page is the target's, with the banner, and the session holds.
        $this->get('/portal')->assertOk()->assertSee('data-impersonation-banner', false)->assertSee('Akun asli: ' . $admin->name);
    }

    public function test_switching_to_staff_drops_the_admin_active_role(): void
    {
        $officer = User::role('front_desk')->firstOrFail();

        $this->actingAs($this->admin())->withSession([ActiveRoles::SESSION_KEY => 'admin'])
            ->post(route('ganti-akun.mulai', $officer), ['alasan' => 'Meninjau tampilan loket'])
            ->assertRedirect('/cp');

        $this->get('/cp')->assertOk();
        $this->assertSame('front_desk', session(ActiveRoles::SESSION_KEY));
    }

    public function test_refusals(): void
    {
        $admin = $this->admin();
        $inactive = User::factory()->create(['is_active' => false]);

        // Own account, inactive account, no reason.
        $this->actingAs($admin)->post(route('ganti-akun.mulai', $admin), ['alasan' => 'Mencoba akun sendiri'])->assertRedirect();
        $this->post(route('ganti-akun.mulai', $inactive), ['alasan' => 'Meninjau akun nonaktif'])->assertRedirect();
        $this->post(route('ganti-akun.mulai', $this->applicant()), [])->assertSessionHasErrors('alasan');
        $this->assertAuthenticatedAs($admin);
        $this->assertSame(0, ImpersonationSession::count());

        // Not an administrator (or not working as one).
        $multi = User::factory()->create();
        $multi->assignRole(['admin', 'front_desk']);
        $multi->forceFill(['active_role_id' => Role::findByName('front_desk', 'web')->id])->save();
        $this->actingAs($multi->fresh())->post(route('ganti-akun.mulai', $this->applicant()), ['alasan' => 'Meninjau keluhan pemohon'])->assertRedirect();
        $this->actingAs(User::role('front_desk')->firstOrFail())->post(route('ganti-akun.mulai', $this->applicant()), ['alasan' => 'Meninjau keluhan pemohon'])->assertRedirect();
        $this->assertSame(0, ImpersonationSession::count());
        $this->assertSame('Ganti akun hanya dari peran aktif Administrator.', collect(session('filament.notifications'))->last()['body']);
    }

    public function test_no_nested_sessions(): void
    {
        $this->actingAs($this->admin())->post(route('ganti-akun.mulai', $this->applicant()), ['alasan' => 'Meninjau keluhan unggah berkas']);
        $other = User::role('front_desk')->firstOrFail();

        $this->post(route('ganti-akun.mulai', $other), ['alasan' => 'Sesi kedua di dalam sesi'])->assertRedirect();

        $this->assertAuthenticatedAs($this->applicant());
        $this->assertSame(1, ImpersonationSession::count());
    }
}
