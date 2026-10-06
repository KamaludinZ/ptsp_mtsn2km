<?php

namespace Tests\Feature;

use App\Filament\Pages\Leadership\DispositionHistory;
use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\User;
use App\Services\TicketService;
use App\Support\ActiveRoles;
use App\Support\DispositionHistory as History;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Riwayat keputusan mencatat dan menampilkan peran aktif pimpinan. */
class DecisionHistoryActiveRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function rejectAsKepalaTu(): array
    {
        $ticket = Ticket::query()->whereHas('service')->firstOrFail();
        $ticket->service->forceFill(['approval_required' => true, 'approval_roles' => [], 'approval_users' => []])->save();
        $ticket->forceFill(['approval_required' => true, 'status' => 'verified', 'approval_status' => 'pending'])->save();

        $user = User::factory()->create(['name' => 'Pak Hadi']);
        $user->assignRole(['kepala_tu', 'front_desk']);
        $user = $user->fresh();
        $this->actingAs($user);
        request()->setUserResolver(fn () => $user);
        request()->attributes->set(ActiveRoles::REQUEST_ATTRIBUTE, 'kepala_tu');

        app(TicketService::class)->decide($ticket->fresh(), false, $user, null, 'Berkas belum lengkap.');

        return [$ticket, $user];
    }

    public function test_ticket_history_names_the_role_the_decision_was_made_in(): void
    {
        [$ticket] = $this->rejectAsKepalaTu();

        $entry = collect(History::forTicket($ticket->fresh()))->last();
        $this->assertSame('Pak Hadi', $entry['actor']);
        $this->assertSame('Kepala Tata Usaha', $entry['role']);
    }

    public function test_decision_history_page_shows_and_filters_by_role(): void
    {
        $this->rejectAsKepalaTu();
        $log = TicketLog::where('action', 'rejected')->latest('id')->firstOrFail();
        $this->assertSame('kepala_tu', $log->acting_role);

        $this->actingAs(User::role('admin')->firstOrFail());
        Livewire::test(DispositionHistory::class)
            ->assertCanSeeTableRecords([$log])
            ->assertTableColumnFormattedStateSet('acting_role', 'Kepala Tata Usaha', $log)
            ->filterTable('acting_role', 'kepala_tu')
            ->assertCanSeeTableRecords([$log])
            ->filterTable('acting_role', 'kepala_sekolah')
            ->assertCanNotSeeTableRecords([$log]);
    }
}
