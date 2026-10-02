<?php

namespace Tests\Feature;

use App\Filament\Pages\Leadership\Approvals;
use App\Filament\Pages\Leadership\DispositionHistory as DispositionHistoryPage;
use App\Filament\Resources\TicketResource\Pages\ViewTicket;
use App\Models\PersuratanMaster;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** Panel aksi disposisi: signature models and the signed sheet. */
class DispositionActionTest extends TestCase
{
    use RefreshDatabase;

    private Ticket $ticket;

    private User $headmaster;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();

        $this->headmaster = User::where('email', 'kepsek@mtsn2malang.sch.id')->firstOrFail();
        $this->ticket = Ticket::firstOrFail();
        $this->ticket->service->update(['approval_required' => true, 'approval_roles' => null, 'approval_users' => null]);
        $this->ticket->update(['status' => 'verified', 'approval_required' => true, 'approval_status' => 'pending']);
    }

    public function test_acknowledged_disposition_needs_no_file(): void
    {
        app(TicketService::class)->dispose($this->ticket, $this->headmaster, 'acknowledged_by', [], 'Untuk diketahui');

        $this->ticket->refresh();
        $this->assertSame('approved', $this->ticket->status);
        $this->assertSame('ack', $this->ticket->signature_type);
        $this->assertDatabaseHas('ticket_logs', [
            'ticket_id' => $this->ticket->id,
            'action' => 'approved',
            'notes' => 'Didisposisi pimpinan (telah didisposisi). Instruksi: Untuk diketahui.',
        ]);
    }

    public function test_signed_sheet_is_attached_to_the_ticket(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('ticket-files/disposisi.pdf', '%PDF-1.4');

        app(TicketService::class)->dispose($this->ticket, $this->headmaster, 'tte_upload', ['tata_usaha'], null, null, 'ticket-files/disposisi.pdf', 'disposisi.pdf');

        $this->assertSame('tte', $this->ticket->fresh()->signature_type);
        $this->assertDatabaseHas('ticket_files', ['ticket_id' => $this->ticket->id, 'file_name' => 'disposisi.pdf', 'uploaded_by' => $this->headmaster->id]);
        $this->assertDatabaseHas('ticket_logs', ['ticket_id' => $this->ticket->id, 'action' => 'file_uploaded']);
    }

    public function test_unknown_signature_model_is_refused(): void
    {
        $this->expectException(\App\Exceptions\TicketActionException::class);

        app(TicketService::class)->dispose($this->ticket, $this->headmaster, 'stempel');
    }

    public function test_leaders_can_print_the_disposition_sheet(): void
    {
        $this->ticket->service->update(['disposition_roles' => ['waka_sarpras']]);

        $this->actingAs($this->headmaster)
            ->get(route('tickets.disposition-sheet', $this->ticket))
            ->assertOk()
            ->assertSee('LEMBAR DISPOSISI')
            ->assertSee($this->ticket->ticket_number)
            ->assertSee('Waka Sarpras');
    }

    public function test_front_desk_cannot_print_the_disposition_sheet(): void
    {
        $this->actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail())
            ->get(route('tickets.disposition-sheet', $this->ticket))
            ->assertForbidden();
    }

    public function test_disposition_history_lists_leadership_decisions(): void
    {
        app(TicketService::class)->dispose($this->ticket, $this->headmaster, 'acknowledged_by', ['tata_usaha'], 'Untuk diketahui');
        $log = $this->ticket->logs()->where('action', 'approved')->firstOrFail();

        $this->actingAs($this->headmaster)->get(DispositionHistoryPage::getUrl())->assertOk();
        Livewire::test(DispositionHistoryPage::class)
            ->assertCanSeeTableRecords([$log])
            ->assertSee('Didisposisi')
            ->assertSee('telah didisposisi')
            ->filterTable('action', 'rejected')
            ->assertCanNotSeeTableRecords([$log]);
    }

    public function test_supervisors_can_open_disposition_history(): void
    {
        $this->actingAs(User::where('email', 'pengawas@mtsn2malang.sch.id')->firstOrFail())
            ->get(DispositionHistoryPage::getUrl())->assertOk();
    }

    public function test_front_desk_cannot_open_disposition_history(): void
    {
        $this->actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail())
            ->get(DispositionHistoryPage::getUrl())->assertForbidden();
    }

    public function test_disposing_from_the_ticket_page_returns_to_the_queue_when_more_wait(): void
    {
        $next = Ticket::whereKeyNot($this->ticket->id)->where('service_id', $this->ticket->service_id)->first()
            ?? Ticket::whereKeyNot($this->ticket->id)->firstOrFail();
        $next->service->update(['approval_required' => true, 'approval_roles' => null, 'approval_users' => null]);
        $next->update(['status' => 'verified', 'approval_required' => true, 'approval_status' => 'pending']);

        $this->actingAs($this->headmaster);

        Livewire::test(ViewTicket::class, ['record' => $this->ticket->id])
            ->callAction('approve', ['signature_model' => 'acknowledged_by'])
            ->assertHasNoActionErrors()
            ->assertRedirect(Approvals::getUrl());

        // The last request in the queue: stay on its page.
        Ticket::whereKeyNot($next->id)->awaitingApproval()->update(['approval_status' => 'rejected']);

        Livewire::test(ViewTicket::class, ['record' => $next->id])
            ->callAction('approve', ['signature_model' => 'acknowledged_by'])
            ->assertNoRedirect();
    }

    public function test_disposition_panel_offers_master_instructions(): void
    {
        PersuratanMaster::create(['type' => 'instruksi_disposisi', 'nama' => 'Segera laporkan hasilnya']);
        $this->actingAs($this->headmaster);

        Livewire::test(ViewTicket::class, ['record' => $this->ticket->id])
            ->mountAction('approve')
            ->assertSee('Segera laporkan hasilnya');
    }

    public function test_quick_instruction_fills_the_instruction_field(): void
    {
        $this->actingAs($this->headmaster);

        Livewire::test(ViewTicket::class, ['record' => $this->ticket->id])
            ->mountAction('approve')
            ->setActionData(['instruction_preset' => 'Untuk dikoordinasikan'])
            ->assertActionDataSet(['instruction' => 'Untuk dikoordinasikan'])
            ->setActionData(['signature_model' => 'acknowledged_by'])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $this->assertStringContainsString('Instruksi: Untuk dikoordinasikan.', $this->ticket->fresh()->approval_notes);
    }

    public function test_typed_instruction_is_kept_without_quick_choice(): void
    {
        $this->actingAs($this->headmaster);

        Livewire::test(ViewTicket::class, ['record' => $this->ticket->id])
            ->callAction('approve', ['signature_model' => 'acknowledged_by', 'instruction' => 'Siapkan rapat koordinasi Senin'])
            ->assertHasNoActionErrors();

        $this->assertStringContainsString('Instruksi: Siapkan rapat koordinasi Senin.', $this->ticket->fresh()->approval_notes);
    }
}
