<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use App\Support\RoleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** Scheduler pengingat permohonan tertunda. */
class PendingTicketReminderTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        RoleAccess::sync();
        $this->travelTo(Carbon::parse('2026-10-15 07:00'));
        $this->officer = User::factory()->create(['user_type' => 'pegawai'])->assignRole('back_office');
    }

    private function titles(User $user): array
    {
        return $user->notifications()->get()->map->title()->sort()->values()->all();
    }

    public function test_overdue_requests_remind_the_assigned_officer_once_a_day(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'in_process', 'approval_required' => false, 'assigned_to_id' => $this->officer->id, 'estimated_completion_date' => '2026-10-12']);
        Ticket::factory()->create(['status' => 'in_process', 'approval_required' => false, 'estimated_completion_date' => '2026-10-20']); // not yet due

        $this->artisan('tickets:remind-pending')->assertSuccessful()->expectsOutput('1 pengingat dikirim.');
        $this->artisan('tickets:remind-pending')->expectsOutput('0 pengingat dikirim.');

        $note = $this->officer->notifications()->sole();
        $this->assertSame('Permohonan melewati target', $note->title());
        $this->assertStringContainsString('terlambat 3 hari', $note->body());
        $this->assertSame($ticket->id, $note->ticket_id);

        $this->travel(1)->days();
        $this->artisan('tickets:remind-pending')->expectsOutput('1 pengingat dikirim.');
    }

    public function test_unassigned_overdue_requests_go_to_the_whole_back_office(): void
    {
        $colleague = User::factory()->create(['user_type' => 'pegawai'])->assignRole('back_office');
        Ticket::factory()->create(['status' => 'verified', 'approval_required' => false, 'assigned_to_id' => null, 'estimated_completion_date' => '2026-10-10']);

        $this->artisan('tickets:remind-pending')->expectsOutput('2 pengingat dikirim.');
        $this->assertSame(['Permohonan melewati target'], $this->titles($colleague));
    }

    public function test_leaders_are_reminded_of_dispositions_waiting_over_a_day(): void
    {
        $leader = User::factory()->create(['user_type' => 'pegawai'])->assignRole('kepala_sekolah');
        $old = Ticket::factory()->create(['status' => 'verified', 'approval_required' => true, 'approval_status' => 'pending', 'estimated_completion_date' => '2026-10-30']);
        $old->service->update(['approval_required' => true, 'approval_roles' => null, 'approval_users' => null]);
        \Illuminate\Support\Facades\DB::table('tickets')->where('id', $old->id)->update(['updated_at' => now()->subDays(2)]);
        $fresh = Ticket::factory()->create(['status' => 'verified', 'approval_required' => true, 'approval_status' => 'pending', 'service_id' => $old->service_id, 'estimated_completion_date' => '2026-10-30']);

        $this->artisan('tickets:remind-pending')->assertSuccessful();

        $notes = $leader->notifications()->get();
        $this->assertSame(['Menunggu disposisi Anda'], $notes->map->title()->unique()->values()->all());
        $this->assertContains($old->id, $notes->pluck('ticket_id'));
        $this->assertNotContains($fresh->id, $notes->pluck('ticket_id'));
    }

    public function test_due_follow_ups_remind_their_officer(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'in_process', 'approval_required' => false, 'assigned_to_id' => $this->officer->id, 'estimated_completion_date' => '2026-10-30']);
        app(TicketService::class)->addNote($ticket, 'Hubungi pemohon soal KK.', $this->officer, 'contact_applicant', false, '2026-10-15');

        $this->artisan('tickets:remind-pending')->expectsOutput('1 pengingat dikirim.');
        $this->assertSame(['Tindak lanjut jatuh tempo'], $this->titles($this->officer));
        $this->assertStringContainsString('Hari ini: Hubungi pemohon soal KK.', $this->officer->notifications()->first()->body());
    }

    public function test_reminders_are_scheduled_on_working_mornings(): void
    {
        $event = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())->first(fn ($e) => $e->description === 'tickets-remind-pending');

        $this->assertNotNull($event);
        $this->assertSame('0 7 * * 1-5', $event->expression);
    }
}
