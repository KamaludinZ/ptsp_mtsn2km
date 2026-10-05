<?php

namespace Tests\Feature;

use App\Filament\Pages\Notifications;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use App\Support\FollowUpReminders;
use App\Support\RoleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

/** Kartu pengingat tindak lanjut. */
class FollowUpReminderTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        RoleAccess::sync();
        $this->travelTo(Carbon::parse('2026-10-15 09:00'));
        $this->officer = User::factory()->create(['user_type' => 'pegawai'])->assignRole('back_office');
    }

    private function plan(Ticket $ticket, ?string $date, string $note = 'Hubungi pemohon.', ?User $by = null): void
    {
        app(TicketService::class)->addNote($ticket, $note, $by ?? $this->officer, 'contact_applicant', false, $date);
    }

    public function test_reminders_cover_overdue_today_and_soon_for_my_requests(): void
    {
        $today = Ticket::factory()->create(['status' => 'in_process', 'assigned_to_id' => $this->officer->id]);
        $soon = Ticket::factory()->create(['status' => 'in_process']);
        $later = Ticket::factory()->create(['status' => 'in_process', 'assigned_to_id' => $this->officer->id]);
        $closed = Ticket::factory()->create(['status' => 'completed', 'assigned_to_id' => $this->officer->id]);
        $notMine = Ticket::factory()->create(['status' => 'in_process']);
        $overdue = Ticket::factory()->create(['status' => 'in_process', 'assigned_to_id' => $this->officer->id]);

        $this->plan($today, '2026-10-15');
        $this->plan($soon, '2026-10-17');                     // noted by me, not assigned
        $this->plan($later, '2026-10-25');                    // beyond 3 days
        $this->plan($closed, '2026-10-15');                   // request closed
        $this->plan($notMine, '2026-10-15', by: User::factory()->create()->assignRole('back_office'));
        $this->travelTo(Carbon::parse('2026-10-10 09:00'));
        $this->plan($overdue, '2026-10-12');
        $this->travelTo(Carbon::parse('2026-10-15 09:00'));

        $items = FollowUpReminders::for($this->officer);

        $this->assertSame([$overdue->id, $today->id, $soon->id], $items->pluck('ticket.id')->all());
        $this->assertSame(['Terlambat 3 hari', 'Hari ini', '2 hari lagi'], $items->map(fn ($i) => FollowUpReminders::label($i))->all());
    }

    public function test_a_newer_note_replaces_or_ends_the_plan(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'in_process', 'assigned_to_id' => $this->officer->id]);
        $this->plan($ticket, '2026-10-15');
        $this->plan($ticket, '2026-10-16', 'Jadwal ulang.');
        $this->assertSame('Besok', FollowUpReminders::label(FollowUpReminders::for($this->officer)->sole()));

        $this->plan($ticket, null, 'Sudah dihubungi, selesai.');
        $this->assertCount(0, FollowUpReminders::for($this->officer));
    }

    public function test_cards_appear_on_the_notification_page(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'in_process', 'assigned_to_id' => $this->officer->id]);
        $this->plan($ticket, '2026-10-15', 'Minta fotokopi KK.');
        $this->actingAs($this->officer);

        $this->get(Notifications::getUrl())->assertOk()
            ->assertSee('Pengingat tindak lanjut (1)')
            ->assertSee($ticket->ticket_number)
            ->assertSee('Minta fotokopi KK.')
            ->assertSee('Menghubungi pemohon');

        Livewire::test(\App\Filament\Widgets\FollowUpReminderCards::class)->assertSee('Hari ini');

        $ticket->update(['status' => 'completed']);
        $this->get(Notifications::getUrl())->assertSee('Tidak ada tindak lanjut yang jatuh tempo');
    }
}
