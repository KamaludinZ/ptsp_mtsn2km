<?php

namespace Tests\Feature;

use App\Enums\DispositionAction;
use App\Enums\SignatureModel;
use App\Filament\Pages\Leadership\Approvals;
use App\Filament\Pages\Leadership\DispositionHistory as DispositionHistoryPage;
use App\Filament\Resources\TicketResource;
use App\Filament\Resources\TicketResource\Pages\ListTickets;
use App\Filament\Resources\TicketResource\Pages\ViewTicket;
use App\Models\DispositionLog;
use App\Models\PersuratanMaster;
use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\User;
use App\Services\DispositionAuthority;
use App\Services\TicketService;
use App\Support\DispositionHistory;
use App\Support\ServiceDisposition;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
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
        // A seeded ticket nobody has disposed yet (unordered first() is not stable on PostgreSQL).
        $this->ticket = Ticket::whereNotIn('id', DispositionLog::select('ticket_id'))->orderBy('id')->firstOrFail();
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

    public function test_disposition_sheet_lists_master_instructions_and_ticks_the_chosen_one(): void
    {
        \App\Models\PersuratanMaster::create(['type' => 'instruksi_disposisi', 'nama' => 'Mohon segera dijadwalkan']);

        $this->actingAs($this->headmaster)
            ->get(route('tickets.disposition-sheet', $this->ticket))
            ->assertOk()
            ->assertSee('Mohon segera dijadwalkan')
            ->assertDontSee('✓ </span>Mohon segera dijadwalkan', false);

        app(TicketService::class)->dispose($this->ticket, $this->headmaster, 'acknowledged_by', ['tata_usaha'], 'Koordinasikan dengan komite');

        $this->get(route('tickets.disposition-sheet', $this->ticket))
            ->assertOk()
            ->assertSee('Mohon segera dijadwalkan')
            ->assertSee('<span class="box">✓</span>Koordinasikan dengan komite', false);
    }

    public function test_disposition_can_be_marked_on_behalf_of_a_leader(): void
    {
        app(TicketService::class)->dispose($this->ticket, $this->headmaster, 'acknowledged_by', ['tata_usaha'], 'Untuk diproses', 'Arahan lewat telepon', acknowledgedBy: '  Kepala Madrasah  Drs. Ahmad ');

        $log = DispositionLog::where('ticket_id', $this->ticket->id)->firstOrFail();
        $this->assertSame('Kepala Madrasah Drs. Ahmad', $log->acknowledged_by_name);
        $this->assertStringContainsString('Telah didisposisi oleh Kepala Madrasah Drs. Ahmad.', $this->ticket->fresh()->approval_notes);
        $this->assertSame('Kepala Madrasah Drs. Ahmad', collect(\App\Support\DispositionHistory::forTicket($this->ticket))->last()['acknowledged_by']);
    }

    public function test_on_behalf_name_is_ignored_for_signed_dispositions(): void
    {
        app(TicketService::class)->dispose($this->ticket, $this->headmaster, 'ttd_upload', acknowledgedBy: 'Seseorang');
        $this->assertNull(DispositionLog::where('ticket_id', $this->ticket->id)->firstOrFail()->acknowledged_by_name);
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

    public function test_history_entries_keep_document_details_and_ip(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('ticket-files/disposisi.pdf', '%PDF-1.4');

        app(TicketService::class)->dispose($this->ticket, $this->headmaster, 'ttd_upload', ['waka_kurikulum'], 'Untuk diproses', null, 'ticket-files/disposisi.pdf', 'disposisi.pdf');

        $decision = $this->ticket->logs()->where('action', 'approved')->firstOrFail();
        $this->assertEquals(['signature_model' => 'ttd_upload', 'recipients' => ['waka_kurikulum'], 'instruction' => 'Untuk diproses', 'signature_type' => 'ttd'], $decision->metadata); // jsonb reorders keys
        $this->assertSame('127.0.0.1', $decision->ip_address);

        $upload = $this->ticket->logs()->where('action', 'file_uploaded')->firstOrFail();
        $this->assertNotNull($upload->ticket_file_id);
        $this->assertSame(['disposisi.pdf'], array_keys($upload->relatedDocuments()));
    }

    public function test_disposition_endpoint_lists_decisions_with_signature_details(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('ticket-files/ttd.pdf', '%PDF-1.4');
        app(TicketService::class)->dispose($this->ticket, $this->headmaster, 'ttd_upload', ['tata_usaha'], 'Untuk diproses', 'Segera', 'ticket-files/ttd.pdf', 'ttd.pdf');

        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $entry = $this->getJson('/api/tiket/' . $this->ticket->ticket_number . '/disposisi')->assertOk()->json('disposisi.0');

        $this->assertSame($this->headmaster->name, $entry['pejabat']);
        $this->assertSame('disposisi', $entry['keputusan']);
        $this->assertSame('ttd_upload', $entry['model_tanda_tangan']);
        $this->assertSame(['tata_usaha'], $entry['penerima']);
        $this->assertSame('Untuk diproses', $entry['instruksi']);
        $this->assertSame('Segera', $entry['catatan']);
        $this->assertStringContainsString('/documents/ticket-files/', $entry['berkas_tanda_tangan']);

        // Applicants cannot read dispositions, not even their own ticket's.
        $applicant = tap(User::factory()->create())->assignRole('umum');
        $this->ticket->update(['user_id' => $applicant->id]);
        Sanctum::actingAs($applicant);
        $this->getJson('/api/tiket/' . $this->ticket->ticket_number . '/disposisi')->assertForbidden();
    }

    public function test_new_requests_follow_the_services_disposition_mode(): void
    {
        $service = $this->ticket->service;
        $applicant = $this->ticket->user;
        $counter = User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail();
        $tickets = app(TicketService::class);

        ServiceDisposition::apply($service, 'none');
        $direct = $tickets->open($service->fresh(), $applicant, $counter, 'offline', 'Tanpa disposisi');
        $this->assertFalse($direct->approval_required);
        $this->assertFalse($direct->needsApproval());
        $this->assertFalse(Ticket::whereKey($direct->id)->awaitingApproval()->exists());

        ServiceDisposition::apply($service, 'kepsek');
        $disposed = $tickets->open($service->fresh(), $applicant, $counter, 'offline', 'Perlu disposisi');
        $this->assertTrue($disposed->approval_required);
        $this->assertSame('pending', $disposed->fresh()->approval_status);
        $this->assertSame('kepsek', $disposed->logs()->where('action', 'created')->first()->metadata['disposition_mode']);

        // Only the headmaster may dispose it, not the head of administration.
        $disposed->update(['status' => 'verified']);
        $this->assertTrue($this->headmaster->can('approve', $disposed->fresh()));
        $this->assertFalse(User::where('email', 'katu@mtsn2malang.sch.id')->firstOrFail()->can('approve', $disposed->fresh()));
    }

    public function test_disposition_is_forwarded_to_the_chosen_back_office_units(): void
    {
        $kurikulum = tap(User::factory()->create(['name' => 'Waka Kurikulum Uji']))->assignRole('waka_kurikulum');
        $sarpras = tap(User::factory()->create())->assignRole('waka_sarpras');

        app(TicketService::class)->dispose($this->ticket, $this->headmaster, 'acknowledged_by', ['waka_kurikulum', 'tata_usaha']);
        $this->assertSame(['waka_kurikulum', 'tata_usaha'], $this->ticket->fresh()->disposition_recipients);

        $this->actingAs($kurikulum);
        $this->assertTrue($kurikulum->isStaff());
        Livewire::test(ListTickets::class, ['activeTab' => 'disposisi-unit'])
            ->assertCanSeeTableRecords([$this->ticket->fresh()]);

        $this->actingAs($sarpras);
        Livewire::test(ListTickets::class, ['activeTab' => 'disposisi-unit'])
            ->assertCanNotSeeTableRecords([$this->ticket->fresh()]);

        $this->actingAs($this->headmaster)
            ->get(TicketResource::getUrl('view', ['record' => $this->ticket]))
            ->assertSee('Diteruskan kepada')
            ->assertSee('Waka Kurikulum, Tata Usaha');
    }

    public function test_without_a_choice_the_services_usual_units_receive_it(): void
    {
        $this->ticket->service->update(['disposition_roles' => ['penjamin_mutu']]);

        app(TicketService::class)->dispose($this->ticket, $this->headmaster, 'acknowledged_by');

        $this->assertSame(['penjamin_mutu'], $this->ticket->fresh()->disposition_recipients);
    }

    public function test_services_without_settings_fall_back_to_the_school_leadership(): void
    {
        // setUp: approval required, no approver roles or users configured.
        $ticket = $this->ticket->fresh();
        $this->assertTrue(ServiceDisposition::usesDefault($ticket->service));
        $this->assertSame('kepsek_tu', $ticket->service->disposition_mode);

        $this->assertTrue($this->headmaster->can('approve', $ticket));
        $this->assertTrue(User::where('email', 'katu@mtsn2malang.sch.id')->firstOrFail()->can('approve', $ticket));
        $this->assertFalse(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail()->can('approve', $ticket));
        $this->assertFalse(tap(User::factory()->create())->assignRole('waka_kurikulum')->can('approve', $ticket));
    }

    public function test_requests_carry_the_signature_recommendation(): void
    {
        $service = $this->ticket->service;
        ServiceDisposition::apply($service, 'kepsek', [], 'tte');
        $ticket = app(TicketService::class)->open($service->fresh(), $this->ticket->user, User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail(), 'offline', 'Legalisir');

        $this->assertSame('tte', $ticket->logs()->where('action', 'created')->first()->metadata['signature_recommendation']);

        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $this->getJson('/api/tiket/' . $ticket->ticket_number . '/riwayat')->assertJsonPath('anjuran_tanda_tangan', 'tte');
    }

    public function test_each_decision_is_stored_in_disposition_logs(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('ticket-files/ttd.pdf', '%PDF-1.4');

        app(TicketService::class)->dispose($this->ticket, $this->headmaster, 'ttd_upload', ['waka_sarpras'], 'Untuk ditindaklanjuti', null, 'ticket-files/ttd.pdf', 'ttd.pdf');

        $row = DispositionLog::where('ticket_id', $this->ticket->id)->sole();
        $this->assertSame(DispositionAction::Disposisi, $row->action);
        $this->assertSame('kepala_sekolah', $row->role);
        $this->assertSame(SignatureModel::TtdUpload, $row->signature_model);
        $this->assertSame(['waka_sarpras'], $row->recipients);
        $this->assertSame('ttd.pdf', $row->signatureFile->file_name);
        $this->assertSame('approved', $row->auditEntry->action);

        $this->expectException(\LogicException::class);
        $row->update(['note' => 'diubah']);
    }

    public function test_disposition_logs_cannot_be_changed_in_the_database(): void
    {
        app(TicketService::class)->dispose($this->ticket, $this->headmaster, 'acknowledged_by');
        $id = DispositionLog::where('ticket_id', $this->ticket->id)->value('id');

        $this->expectException(QueryException::class);
        DB::table('disposition_logs')->where('id', $id)->update(['note' => 'diubah']);
    }

    public function test_earlier_decisions_are_copied_by_the_migration(): void
    {
        $migration = require database_path('migrations/2026_10_05_110000_create_disposition_logs_table.php');
        $migration->down();
        (new TicketLog())->forceFill([
            'ticket_id' => $this->ticket->id,
            'action' => 'approved',
            'performed_by' => $this->headmaster->id,
            'notes' => 'Disetujui pimpinan (TTE).',
            'metadata' => ['signature_model' => 'tte_upload', 'recipients' => ['tata_usaha'], 'instruction' => 'Untuk diproses'],
        ])->save();
        (new TicketLog())->forceFill(['ticket_id' => $this->ticket->id, 'action' => 'rejected', 'performed_by' => $this->headmaster->id, 'notes' => 'Ditolak pimpinan: berkas kurang.'])->save();

        $migration->up();

        $rows = DispositionLog::where('ticket_id', $this->ticket->id)->orderBy('id')->get();
        $this->assertSame([DispositionAction::Disposisi, DispositionAction::Reject], $rows->pluck('action')->all());
        $this->assertSame(SignatureModel::TteUpload, $rows[0]->signature_model);
        $this->assertSame(['tata_usaha'], $rows[0]->recipients);
        $this->assertSame('Ditolak pimpinan: berkas kurang.', $rows[1]->note);
    }

    public function test_signature_model_enum_matches_the_ticket_codes(): void
    {
        foreach (SignatureModel::cases() as $model) {
            $this->assertSame(ServiceDisposition::SIGNATURE_TYPES[$model->value], $model->ticketCode());
            $this->assertSame(ServiceDisposition::SIGNATURE_MODELS[$model->value], $model->label());
        }
        $this->assertFalse(SignatureModel::AcknowledgedBy->needsFile());
        $this->assertSame('Ditolak', DispositionAction::Reject->label());
    }

    public function test_disposition_authority_follows_the_mode(): void
    {
        $authority = app(DispositionAuthority::class);
        $tu = User::where('email', 'katu@mtsn2malang.sch.id')->firstOrFail();
        $service = $this->ticket->service;

        ServiceDisposition::apply($service, 'tu');
        $ticket = $this->ticket->fresh();
        $this->assertTrue($authority->canDispose($tu, $ticket));
        $this->assertFalse($authority->canDispose($this->headmaster, $ticket));
        $this->assertSame([$tu->id], $authority->disposers($ticket)->pluck('id')->all());

        $service->update(['approval_users' => [(string) $this->headmaster->id]]);
        $this->assertTrue($authority->canDispose($this->headmaster, $this->ticket->fresh()));

        // Switching the service to "tanpa disposisi" must not strand tickets already waiting.
        ServiceDisposition::apply($service, 'none');
        $this->assertTrue($authority->canDispose($this->headmaster, $this->ticket->fresh()));

        $this->ticket->update(['approval_required' => false]);
        $this->assertFalse($authority->canDispose($this->headmaster, $this->ticket->fresh()));
        $this->assertTrue($authority->disposers($this->ticket->fresh())->isEmpty());
    }

    public function test_disposition_policy(): void
    {
        app(TicketService::class)->dispose($this->ticket, $this->headmaster, 'ttd_upload');
        $ttd = DispositionLog::where('ticket_id', $this->ticket->id)->latest('id')->firstOrFail();
        $admin = User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail();
        $tu = User::where('email', 'katu@mtsn2malang.sch.id')->firstOrFail();
        $staff = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $applicant = tap(User::factory()->create())->assignRole('umum');

        $this->assertTrue($this->headmaster->can('uploadSignature', $ttd));
        $this->assertTrue($admin->can('uploadSignature', $ttd));
        $this->assertFalse($tu->can('uploadSignature', $ttd));
        $this->assertTrue($staff->can('view', $ttd));
        $this->assertFalse($applicant->can('view', $ttd));
        $this->assertFalse($admin->can('update', $ttd));
        $this->assertFalse($admin->can('delete', $ttd));
    }

    public function test_acknowledged_dispositions_take_no_signature_upload(): void
    {
        app(TicketService::class)->dispose($this->ticket, $this->headmaster, 'acknowledged_by');
        $ack = DispositionLog::where('ticket_id', $this->ticket->id)->firstOrFail();

        $this->assertFalse($this->headmaster->can('uploadSignature', $ack));
    }

    public function test_queue_endpoint_lists_what_waits_for_the_leader(): void
    {
        Sanctum::actingAs($this->headmaster);
        $numbers = collect($this->getJson('/api/disposisi/antrean')->assertOk()->json('data'))->pluck('nomor_tiket');
        $this->assertTrue($numbers->contains($this->ticket->ticket_number));

        app(TicketService::class)->dispose($this->ticket, $this->headmaster, 'acknowledged_by');
        $numbers = collect($this->getJson('/api/disposisi/antrean')->json('data'))->pluck('nomor_tiket');
        $this->assertFalse($numbers->contains($this->ticket->ticket_number));

        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $this->getJson('/api/disposisi/antrean')->assertOk()->assertJsonPath('total', 0);
    }

    public function test_leaders_dispose_or_reject_through_the_api(): void
    {
        $url = '/api/disposisi/' . $this->ticket->ticket_number;

        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $this->postJson($url, ['keputusan' => 'disposisi', 'model_tanda_tangan' => 'tte_upload'])->assertForbidden();

        Sanctum::actingAs($this->headmaster);
        $this->postJson($url, ['keputusan' => 'disposisi'])->assertUnprocessable()->assertJsonValidationErrors('model_tanda_tangan');
        $this->postJson($url, ['keputusan' => 'tolak'])->assertUnprocessable()->assertJsonValidationErrors('catatan');

        $this->postJson($url, ['keputusan' => 'disposisi', 'model_tanda_tangan' => 'tte_upload', 'penerima' => ['tata_usaha'], 'instruksi' => 'Untuk diproses'])
            ->assertOk()
            ->assertJsonPath('status', 'approved')
            ->assertJsonPath('disposisi.signature_model', 'tte_upload')
            ->assertJsonPath('disposisi.recipients', ['tata_usaha']);

        // Deciding twice is refused with the service's message.
        $this->postJson($url, ['keputusan' => 'tolak', 'catatan' => 'x'])->assertUnprocessable()->assertJsonPath('message', 'Tiket ini tidak sedang menunggu disposisi.');
    }

    public function test_disposition_on_behalf_of_a_leader_through_the_api(): void
    {
        $url = '/api/disposisi/' . $this->ticket->ticket_number;
        Sanctum::actingAs($this->headmaster);

        $this->postJson($url, ['keputusan' => 'disposisi', 'model_tanda_tangan' => 'acknowledged_by'])
            ->assertUnprocessable()->assertJsonValidationErrors('didisposisi_oleh');

        $this->postJson($url, ['keputusan' => 'disposisi', 'model_tanda_tangan' => 'acknowledged_by', 'didisposisi_oleh' => 'Kepala Madrasah', 'penerima' => ['tata_usaha']])
            ->assertOk()
            ->assertJsonPath('disposisi.acknowledged_by', 'Kepala Madrasah');

        $this->getJson('/api/tiket/' . $this->ticket->ticket_number . '/disposisi')
            ->assertOk()
            ->assertJsonPath('disposisi.0.didisposisi_oleh', 'Kepala Madrasah');
    }

    public function test_leader_uploads_the_signed_sheet_later(): void
    {
        Storage::fake('local');
        app(TicketService::class)->dispose($this->ticket, $this->headmaster, 'ttd_upload');
        $disposition = DispositionLog::where('ticket_id', $this->ticket->id)->firstOrFail();
        $url = '/api/disposisi/riwayat/' . $disposition->id . '/tanda-tangan';

        Sanctum::actingAs(User::where('email', 'katu@mtsn2malang.sch.id')->firstOrFail());
        $this->post($url, ['berkas' => UploadedFile::fake()->create('ttd.pdf', 50, 'application/pdf')], ['Accept' => 'application/json'])->assertForbidden();

        Sanctum::actingAs($this->headmaster);
        $this->post($url, ['berkas' => UploadedFile::fake()->create('virus.exe', 5, 'application/x-msdownload')], ['Accept' => 'application/json'])
            ->assertUnprocessable();

        $this->post($url, ['berkas' => UploadedFile::fake()->create('lembar-ttd.pdf', 50, 'application/pdf')], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('berkas', 'lembar-ttd.pdf');

        $entry = collect(DispositionHistory::forTicket($this->ticket->fresh()))->firstWhere('id', $disposition->id);
        $this->assertNotNull($entry['signature_file']);
        $this->assertDatabaseHas('ticket_logs', ['ticket_id' => $this->ticket->id, 'action' => 'file_uploaded', 'notes' => 'Lembar disposisi bertanda tangan lembar-ttd.pdf diunggah.']);
    }
}
