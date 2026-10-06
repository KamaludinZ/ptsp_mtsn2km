<?php

namespace Tests\Feature;

use App\Models\ImpersonationSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/** Sesi ganti akun dan aktivitas selama sesi tercatat di activity log. */
class ImpersonationActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    public function test_start_and_end_are_recorded_with_who_why_and_how(): void
    {
        $admin = User::role('admin')->firstOrFail();
        $applicant = User::where('email', 'budi.santoso@email.com')->firstOrFail();

        $this->actingAs($admin)->post(route('ganti-akun.mulai', $applicant), ['alasan' => 'Meninjau keluhan unggah berkas']);
        $this->post(route('ganti-akun.selesai'));

        $entries = Activity::inLog('impersonation')->oldest('id')->get();
        $this->assertSame(['started', 'ended'], $entries->pluck('event')->all());

        [$started, $ended] = $entries;
        $this->assertTrue($started->causer->is($admin));
        $this->assertTrue($started->subject->is($applicant));
        $this->assertSame('Mulai ganti akun ke ' . $applicant->name, $started->description);
        $this->assertSame('Meninjau keluhan unggah berkas', $started->properties['alasan']);
        $this->assertSame(ImpersonationSession::sole()->id, $started->properties['sesi_id']);
        $this->assertSame('selesai', $ended->properties['cara_berakhir']);
        $this->assertStringContainsString('Kembali ke akun admin', $ended->description);
    }

    public function test_actions_taken_in_the_borrowed_account_name_the_admin(): void
    {
        $admin = User::role('admin')->firstOrFail();
        $applicant = User::where('email', 'budi.santoso@email.com')->firstOrFail();
        $this->actingAs($admin)->post(route('ganti-akun.mulai', $applicant), ['alasan' => 'Memperbaiki nama pemohon']);

        // Something the borrowed account does that is logged (a profile change).
        $applicant->fresh()->update(['name' => 'Budi S.']);

        $change = Activity::query()->where('log_name', '!=', 'impersonation')->latest('id')->firstOrFail();
        $this->assertSame($admin->name, $change->properties['impersonated_by']['admin']);
        $this->assertSame(ImpersonationSession::sole()->id, $change->properties['impersonated_by']['sesi_id']);

        // Back in the admin's own account, nothing is tagged.
        $this->post(route('ganti-akun.selesai'));
        $applicant->fresh()->update(['name' => 'Budi Santoso']);
        $this->assertArrayNotHasKey('impersonated_by', Activity::query()->where('log_name', '!=', 'impersonation')->latest('id')->firstOrFail()->properties->all());
    }

    public function test_signing_out_and_expiry_are_recorded_too(): void
    {
        $admin = User::role('admin')->firstOrFail();
        $applicant = User::where('email', 'budi.santoso@email.com')->firstOrFail();

        $this->actingAs($admin)->post(route('ganti-akun.mulai', $applicant), ['alasan' => 'Meninjau keluhan unggah berkas']);
        $this->post('/logout');

        $this->assertSame('keluar', Activity::inLog('impersonation')->where('event', 'ended')->sole()->properties['cara_berakhir']);
    }
}
