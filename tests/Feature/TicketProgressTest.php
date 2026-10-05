<?php

namespace Tests\Feature;

use App\Filament\Resources\TicketResource\Pages\ViewTicket;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use App\Support\RoleAccess;
use App\Support\TicketProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Detail permohonan: tahapan (stepper) and the time left until the target. */
class TicketProgressTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        RoleAccess::sync();
        $this->officer = User::factory()->create(['user_type' => 'pegawai'])->assignRole('back_office');
    }

    private function states(Ticket $ticket): array
    {
        return collect(TicketProgress::steps($ticket->fresh()))->mapWithKeys(fn ($step) => [$step['label'] => $step['state']])->all();
    }

    public function test_steps_follow_the_ticket_from_submission_to_completion(): void
    {
        $ticket = Ticket::factory()->create(['service_id' => Service::factory()->create()->id, 'status' => 'submitted', 'approval_required' => false]);
        $this->assertSame(['Diajukan' => 'done', 'Diverifikasi' => 'current', 'Diproses' => 'upcoming', 'Selesai' => 'upcoming'], $this->states($ticket));

        $service = app(TicketService::class);
        $service->changeStatus($ticket, 'verified', 'Lengkap.', $this->officer);
        $service->changeStatus($ticket->fresh(), 'in_process', 'Diproses.', $this->officer);
        $this->assertSame(['Diajukan' => 'done', 'Diverifikasi' => 'done', 'Diproses' => 'done', 'Selesai' => 'current'], $this->states($ticket));
        $this->assertNotNull(collect(TicketProgress::steps($ticket->fresh()))->firstWhere('key', 'verified')['at']);
    }

    public function test_disposition_step_appears_and_rejection_stops_there(): void
    {
        $ticket = Ticket::factory()->create(['service_id' => Service::factory()->create()->id, 'status' => 'verified', 'approval_required' => true, 'approval_status' => 'pending']);
        $this->assertSame('current', $this->states($ticket)['Disposisi pimpinan']);

        $ticket->update(['status' => 'rejected', 'approval_status' => 'rejected']);
        $states = $this->states($ticket);
        $this->assertSame('stopped', $states['Ditolak']);
        $this->assertSame(['done', 'done', 'stopped', 'upcoming', 'upcoming'], array_values($states));
    }

    public function test_deadline_counts_days_left_or_late(): void
    {
        $this->travelTo(today()->setTime(12, 0));
        $ticket = Ticket::factory()->create(['status' => 'in_process', 'estimated_completion_date' => today()->addDays(5)]);
        $this->assertSame(['label' => 'Sisa 5 hari', 'color' => 'success'], TicketProgress::deadline($ticket));

        $ticket->estimated_completion_date = today();
        $this->assertSame('Jatuh tempo hari ini', TicketProgress::deadline($ticket)['label']);
        $ticket->estimated_completion_date = today()->subDays(2);
        $this->assertSame(['label' => 'Terlambat 2 hari', 'color' => 'danger'], TicketProgress::deadline($ticket));
        $ticket->status = 'completed';
        $this->assertNull(TicketProgress::deadline($ticket));
    }

    public function test_detail_page_shows_the_stepper(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'submitted', 'estimated_completion_date' => today()->addDays(3)]);

        $this->actingAs($this->officer);
        Livewire::test(ViewTicket::class, ['record' => $ticket->id])
            ->assertSee('Tahapan permohonan')
            ->assertSee('Diverifikasi')
            ->assertSee('Sedang berjalan')
            ->assertSee('Sisa 3 hari');
    }

    public function test_status_history_lists_changes_with_time_in_each_status(): void
    {
        $this->travelTo(now()->startOfDay()->setTime(8, 0));
        $ticket = Ticket::factory()->create(['service_id' => Service::factory()->create()->id, 'status' => 'submitted', 'approval_required' => false]);
        $service = app(TicketService::class);

        $this->travel(2)->hours();
        $service->changeStatus($ticket, 'verified', 'Berkas lengkap.', $this->officer);
        $this->travel(1)->days();
        $service->changeStatus($ticket->fresh(), 'in_process', 'Mulai diproses.', $this->officer);
        $this->travel(30)->minutes();

        $history = TicketProgress::statusHistory($ticket->fresh());
        $verified = collect($history)->firstWhere('to', 'verified');
        $this->assertSame('1 hari', $verified['duration']);
        $this->assertSame($this->officer->name, $verified['actor']);
        $this->assertSame('Berkas lengkap.', $verified['notes']);
        $last = end($history);
        $this->assertSame(['in_process', true, '30 menit'], [$last['to'], $last['current'], $last['duration']]);

        $this->actingAs($this->officer);
        Livewire::test(ViewTicket::class, ['record' => $ticket->id])
            ->assertSee('Riwayat status')
            ->assertSee('Lama di status ini')
            ->assertSee('Saat ini');
    }

    public function test_duration_is_human_readable(): void
    {
        $from = now();
        $this->assertSame('< 1 menit', TicketProgress::duration($from, $from->copy()->addSeconds(20)));
        $this->assertSame('2 jam 5 menit', TicketProgress::duration($from, $from->copy()->addMinutes(125)));
        $this->assertSame('3 hari 4 jam', TicketProgress::duration($from, $from->copy()->addDays(3)->addHours(4)));
    }

    public function test_follow_up_note_is_internal_by_default(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'in_process']);
        $this->actingAs($this->officer);

        Livewire::test(ViewTicket::class, ['record' => $ticket->id])
            ->callAction('note', ['type' => 'coordination', 'note' => 'Dikoordinasikan dengan TU.', 'to_applicant' => false, 'next_follow_up_at' => today()->addDays(2)->toDateString()])
            ->assertHasNoActionErrors()
            ->assertSee('Koordinasi dengan unit lain')
            ->assertSee('Internal');

        $log = $ticket->logs()->latest('id')->first();
        $this->assertSame('note_added', $log->action);
        $this->assertSame(['follow_up_type' => 'coordination', 'next_follow_up_at' => today()->addDays(2)->toDateString()], $log->metadata);
        $this->assertNotContains($log->action, \App\Models\TicketLog::APPLICANT_VISIBLE);
    }

    public function test_follow_up_note_can_be_shown_to_the_applicant(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'in_process']);
        $this->actingAs($this->officer);

        Livewire::test(ViewTicket::class, ['record' => $ticket->id])
            ->callAction('note', ['type' => 'request_documents', 'note' => 'Mohon unggah fotokopi KK.', 'to_applicant' => true])
            ->assertHasNoActionErrors()
            ->assertSee('Terlihat oleh pemohon');

        $log = $ticket->logs()->latest('id')->first();
        $this->assertSame('applicant_note', $log->action);
        $this->assertContains('applicant_note', \App\Models\TicketLog::APPLICANT_VISIBLE);
    }

    public function test_follow_up_note_validates_its_fields(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'in_process']);
        $this->actingAs($this->officer);

        Livewire::test(ViewTicket::class, ['record' => $ticket->id])
            ->callAction('note', ['type' => 'internal', 'note' => 'ok'])
            ->assertHasActionErrors(['note' => 'min']);

        $this->expectException(\App\Exceptions\TicketActionException::class);
        app(TicketService::class)->addNote($ticket, 'Catatan lama.', $this->officer, 'internal', false, today()->subDay()->toDateString());
    }

    public function test_status_control_offers_only_valid_next_statuses(): void
    {
        $service = app(TicketService::class);
        $ticket = Ticket::factory()->create(['status' => 'submitted', 'approval_required' => false]);

        $this->assertSame(['verified', 'in_process', 'rejected', 'cancelled'], array_keys($service->nextStatuses($ticket, $this->officer)));
        $ticket->status = 'completed';
        $this->assertSame([], $service->nextStatuses($ticket, $this->officer));
        $admin = User::factory()->create()->assignRole('admin');
        $this->assertSame(['in_process'], array_keys($service->nextStatuses($ticket, $admin)));

        $waiting = Ticket::factory()->create(['status' => 'in_process', 'approval_required' => true, 'approval_status' => 'pending']);
        $this->assertArrayNotHasKey('completed', $service->nextStatuses($waiting, $this->officer));
    }

    public function test_invalid_or_unexplained_changes_are_refused(): void
    {
        $service = app(TicketService::class);
        $ticket = Ticket::factory()->create(['status' => 'submitted', 'approval_required' => false]);

        foreach ([['completed', 'Selesai cepat.', 'tidak dapat diubah'], ['submitted', 'Sama.', 'sudah berstatus'], ['rejected', 'Tidak.', 'alasan penolakan']] as [$status, $notes, $message]) {
            try {
                $service->changeStatus($ticket->fresh(), $status, $notes, $this->officer);
                $this->fail("{$status} should be refused");
            } catch (\App\Exceptions\TicketActionException $e) {
                $this->assertStringContainsString($message, $e->getMessage());
            }
        }
        $this->assertSame('submitted', $ticket->fresh()->status);
    }

    public function test_officer_changes_status_from_the_detail_page(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'submitted', 'approval_required' => false]);
        $this->actingAs($this->officer);

        Livewire::test(ViewTicket::class, ['record' => $ticket->id])
            ->mountAction('changeStatus')
            ->assertSee('Status saat ini: Diajukan')
            ->setActionData(['status' => 'rejected', 'notes' => 'Kurang'])
            ->callMountedAction()
            ->assertHasActionErrors(['notes' => 'min']);

        Livewire::test(ViewTicket::class, ['record' => $ticket->id])
            ->callAction('changeStatus', ['status' => 'verified', 'notes' => 'Berkas lengkap.'])
            ->assertHasNoActionErrors()
            ->assertNotified('Status menjadi Diverifikasi.');
        $this->assertSame('verified', $ticket->fresh()->status);

        $ticket->update(['status' => 'cancelled']);
        Livewire::test(ViewTicket::class, ['record' => $ticket->id])->assertActionDisabled('changeStatus');
    }

    public function test_status_changes_from_the_list_without_opening_the_ticket(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'submitted', 'approval_required' => false]);
        $this->actingAs($this->officer);
        $list = \App\Filament\Resources\TicketResource\Pages\ListTickets::class;

        Livewire::test($list)
            ->set('activeTab', 'semua')
            ->assertTableActionVisible('status_verified', $ticket)
            ->assertTableActionHidden('status_completed', $ticket)
            ->assertSee('data-ticket-status', false)
            ->assertSee('replaceChildren', false)
            ->callTableAction('status_verified', $ticket)
            ->assertHasNoTableActionErrors()
            ->assertNotified("{$ticket->ticket_number}: Diverifikasi");
        $this->assertSame('verified', $ticket->fresh()->status);
        $this->assertSame('Status diubah menjadi Diverifikasi dari daftar permohonan.', $ticket->logs()->latest('id')->value('notes'));

        Livewire::test($list)
            ->set('activeTab', 'semua')
            ->callTableAction('status_rejected', $ticket, ['notes' => 'Singkat'])
            ->assertHasTableActionErrors(['notes' => 'min'])
            ->callTableAction('status_rejected', $ticket, ['notes' => 'Berkas tidak sesuai ketentuan.'])
            ->assertHasNoTableActionErrors();
        $this->assertSame('rejected', $ticket->fresh()->status);

        Livewire::test($list)->set('activeTab', 'semua')->assertTableActionHidden('status_in_process', $ticket->fresh());
    }

    public function test_keyword_search_covers_applicant_whatsapp_service_and_text(): void
    {
        $siti = User::factory()->create(['name' => 'Siti Aminah', 'email' => 'siti@contoh.id', 'whatsapp_number' => '+62 812-3456-7890']);
        $legalisir = Service::factory()->create(['name' => 'Legalisir Ijazah']);
        $a = Ticket::factory()->create(['user_id' => $siti->id, 'service_id' => $legalisir->id, 'notes' => 'Untuk daftar SMA']);
        $b = Ticket::factory()->create(['user_id' => User::factory()->create(['name' => 'Budi Santoso', 'whatsapp_number' => '0899111222'])->id, 'notes' => 'Surat pindah']);
        $this->actingAs($this->officer);

        $search = fn (string $term) => Livewire::test(\App\Filament\Resources\TicketResource\Pages\ListTickets::class)
            ->set('activeTab', 'semua')->set('tableSearch', $term);

        $search('SITI')->assertCanSeeTableRecords([$a])->assertCanNotSeeTableRecords([$b]);
        $search('08123456')->assertCanSeeTableRecords([$a])->assertCanNotSeeTableRecords([$b]);
        $search('62899111')->assertCanSeeTableRecords([$b])->assertCanNotSeeTableRecords([$a]);
        $search('legalisir sma')->assertCanSeeTableRecords([$a])->assertCanNotSeeTableRecords([$b]);
        $search('legalisir pindah')->assertCanNotSeeTableRecords([$a, $b]);
        $search(strtolower($b->ticket_number))->assertCanSeeTableRecords([$b])->assertCanNotSeeTableRecords([$a]);
        $search('100%')->assertCanNotSeeTableRecords([$a, $b]);
    }

    public function test_search_words_and_phone_variants(): void
    {
        $this->assertSame(['legalisir', 'ijazah'], \App\Support\TicketSearch::words('  legalisir  ijazah x '));
        $this->assertSame(['08123', '628123'], \App\Support\TicketSearch::phoneVariants('0812-3'));
        $this->assertSame(['628123', '08123'], \App\Support\TicketSearch::phoneVariants('+62 8123'));
        $this->assertSame([], \App\Support\TicketSearch::phoneVariants('PTSP-2026'));
    }

    public function test_list_filters_by_service_status_and_date_period(): void
    {
        $this->travelTo(today()->setTime(12, 0));
        $legalisir = Service::factory()->create(['name' => 'Legalisir']);
        $mutasi = Service::factory()->create(['name' => 'Mutasi']);
        $today = Ticket::factory()->create(['service_id' => $legalisir->id, 'status' => 'submitted']);
        $old = Ticket::factory()->create(['service_id' => $mutasi->id, 'status' => 'in_process', 'created_at' => now()->subDays(20)]);
        $done = Ticket::factory()->create(['service_id' => $mutasi->id, 'status' => 'completed', 'created_at' => now()->subDays(3)]);
        $this->actingAs($this->officer);

        // Filters persist in the session, so each check starts from a clean slate.
        $list = fn () => Livewire::test(\App\Filament\Resources\TicketResource\Pages\ListTickets::class)->set('activeTab', 'semua')->resetTableFilters();

        $list()->filterTable('service_id', [$mutasi->id])->assertCanSeeTableRecords([$old, $done])->assertCanNotSeeTableRecords([$today]);
        $list()->filterTable('status', ['submitted', 'completed'])->assertCanSeeTableRecords([$today, $done])->assertCanNotSeeTableRecords([$old]);
        $list()->filterTable('created_at', ['period' => 'today'])->assertCanSeeTableRecords([$today])->assertCanNotSeeTableRecords([$old, $done])
            ->assertSee('Diajukan: Hari ini');
        $list()->filterTable('created_at', ['period' => 'last_7_days'])->assertCanSeeTableRecords([$today, $done])->assertCanNotSeeTableRecords([$old]);
        $list()->filterTable('created_at', ['period' => 'custom', 'from' => now()->subDays(25)->toDateString(), 'until' => now()->subDays(10)->toDateString()])
            ->assertCanSeeTableRecords([$old])->assertCanNotSeeTableRecords([$today, $done]);
        $list()->filterTable('service_id', [$mutasi->id])->filterTable('status', ['in_process'])
            ->assertCanSeeTableRecords([$old])->assertCanNotSeeTableRecords([$today, $done]);
    }

    public function test_date_periods_resolve_to_ranges(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-03-31 10:00'));
        $range = fn (array $data) => array_map(fn ($d) => $d?->toDateString(), \App\Filament\Resources\TicketResource::dateRange($data));

        $this->assertSame(['2026-03-25', '2026-03-31'], $range(['period' => 'last_7_days']));
        $this->assertSame(['2026-02-01', '2026-02-28'], $range(['period' => 'last_month']));
        $this->assertSame(['2026-01-01', '2026-03-31'], $range(['period' => 'this_year']));
        $this->assertSame(['2026-03-02', null], $range(['period' => 'custom', 'from' => '2026-03-02']));
        $this->assertSame([null, null], $range([]));
    }

    public function test_active_filters_are_summarised_and_reset_in_one_click(): void
    {
        $legalisir = Service::factory()->create(['name' => 'Legalisir']);
        $a = Ticket::factory()->create(['service_id' => $legalisir->id, 'status' => 'submitted']);
        $b = Ticket::factory()->create(['status' => 'in_process']);
        $this->actingAs($this->officer);

        Livewire::test(\App\Filament\Resources\TicketResource\Pages\ListTickets::class)
            ->set('activeTab', 'semua')
            ->resetTableFilters()
            ->assertTableActionHidden('resetAll')
            ->filterTable('status', ['submitted'])
            ->filterTable('service_id', [$legalisir->id])
            ->set('tableSearch', 'legal')
            ->assertSee('2 filter aktif')
            ->assertSee('kata kunci &quot;legal&quot;', false)
            ->assertSee('1 permohonan ditemukan')
            ->assertSee('Status: Diajukan')
            ->assertSee('Jenis layanan: Legalisir')
            ->assertCanNotSeeTableRecords([$b])
            ->assertTableActionVisible('resetAll')
            ->callTableAction('resetAll')
            ->assertSet('tableSearch', '')
            ->assertCanSeeTableRecords([$a, $b])
            ->assertDontSee('filter aktif')
            ->assertTableActionHidden('resetAll');
    }
}
