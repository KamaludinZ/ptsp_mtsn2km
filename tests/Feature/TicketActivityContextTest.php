<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\User;
use App\Services\TicketService;
use App\Support\ActiveRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/** Aktivitas selama peran aktif membawa konteks tiket dan peran (activity_log). */
class TicketActivityContextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function actIn(User $user, string $role): void
    {
        $this->actingAs($user);
        request()->setUserResolver(fn () => $user);
        request()->attributes->set(ActiveRoles::REQUEST_ATTRIBUTE, $role);
    }

    public function test_a_decision_is_recorded_with_ticket_service_and_role(): void
    {
        $ticket = Ticket::query()->whereHas('service')->with('service')->firstOrFail();
        $ticket->service->forceFill(['approval_required' => true, 'approval_roles' => [], 'approval_users' => []])->save();
        $ticket->forceFill(['approval_required' => true, 'status' => 'verified', 'approval_status' => 'pending'])->save();
        $user = User::factory()->create();
        $user->assignRole(['kepala_tu', 'front_desk']);
        $this->actIn($user->fresh(), 'kepala_tu');

        app(TicketService::class)->decide($ticket->fresh(), false, $user->fresh(), null, 'Berkas belum lengkap.');

        $entry = Activity::inLog('ticket')->where('event', 'rejected')->sole();
        $this->assertTrue($entry->causer->is($user));
        $this->assertTrue($entry->subject->is($ticket));
        $this->assertSame($ticket->ticket_number, $entry->properties['tiket']);
        $this->assertSame($ticket->service->name, $entry->properties['layanan']);
        $this->assertSame('kepala_tu', $entry->properties['peran_aktif']);
        $this->assertSame('verified', $entry->properties['status_awal']);
        $this->assertStringContainsString($ticket->ticket_number, $entry->description);
        $this->assertSame(TicketLog::where('action', 'rejected')->latest('id')->value('id'), $entry->properties['riwayat_id']);
    }

    public function test_applicant_and_system_entries_are_not_copied(): void
    {
        $ticket = Ticket::query()->firstOrFail();
        $applicant = User::where('email', 'budi.santoso@email.com')->firstOrFail();
        $before = Activity::inLog('ticket')->count();

        TicketLog::create(['ticket_id' => $ticket->id, 'action' => 'applicant_note', 'performed_by' => $applicant->id, 'notes' => 'Catatan']);
        TicketLog::create(['ticket_id' => $ticket->id, 'action' => 'note', 'performed_by' => null, 'notes' => 'Sistem']);

        $this->assertSame($before, Activity::inLog('ticket')->count());
    }

    public function test_other_activity_by_staff_carries_the_active_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole(['admin', 'front_desk']);
        $this->actIn($user->fresh(), 'admin');

        $entry = activity('audit')->causedBy($user->fresh())->log('Mengubah pengaturan');

        $this->assertSame('admin', $entry->fresh()->properties['peran_aktif']);
        // An applicant has no active role.
        $applicant = User::where('email', 'budi.santoso@email.com')->firstOrFail();
        $this->assertArrayNotHasKey('peran_aktif', activity('audit')->causedBy($applicant)->log('Pemohon mengubah profil')->fresh()->properties->all());
    }
}
