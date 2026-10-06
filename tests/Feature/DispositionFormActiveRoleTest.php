<?php

namespace Tests\Feature;

use App\Filament\Resources\TicketResource\Pages\ViewTicket;
use App\Models\DispositionLog;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Form disposisi: pimpinan memutuskan atas nama peran aktifnya. */
class DispositionFormActiveRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function awaitingTicket(): Ticket
    {
        $ticket = Ticket::query()->whereHas('service')->firstOrFail();
        $ticket->service->forceFill(['approval_required' => true, 'approval_roles' => [], 'approval_users' => []])->save();
        $ticket->forceFill(['approval_required' => true, 'status' => 'verified', 'approval_status' => 'pending'])->save();

        return $ticket->fresh();
    }

    private function leader(): User
    {
        $user = User::factory()->create(['name' => 'Bu Sari']);
        $user->assignRole(['kepala_tu', 'front_desk']);
        $user->forceFill(['active_role_id' => Role::findByName('kepala_tu', 'web')->id])->save();

        return $user->fresh();
    }

    public function test_form_shows_the_role_the_leader_disposes_as(): void
    {
        $ticket = $this->awaitingTicket();
        $this->actingAs($this->leader());

        Livewire::test(ViewTicket::class, ['record' => $ticket->getRouteKey()])
            ->mountAction('approve')
            ->assertSee('Didisposisi sebagai')
            ->assertSeeHtml('data-acting-role="kepala_tu"')
            ->assertSee('Diputuskan sebagai Kepala Tata Usaha.')
            ->setActionData(['signature_model' => 'acknowledged_by'])
            ->assertActionDataSet(['acknowledged_by' => 'Kepala Tata Usaha — Bu Sari']);
    }

    public function test_disposition_is_recorded_under_the_active_role(): void
    {
        $ticket = $this->awaitingTicket();
        $this->actingAs($this->leader());

        Livewire::test(ViewTicket::class, ['record' => $ticket->getRouteKey()])
            ->callAction('approve', ['signature_model' => 'acknowledged_by', 'acknowledged_by' => 'Kepala Tata Usaha — Bu Sari', 'recipients' => []])
            ->assertHasNoActionErrors();

        $log = DispositionLog::where('ticket_id', $ticket->id)->latest('id')->firstOrFail();
        $this->assertSame('kepala_tu', $log->role);
        $this->assertSame('Kepala Tata Usaha — Bu Sari', $log->acknowledged_by_name);
    }
}
