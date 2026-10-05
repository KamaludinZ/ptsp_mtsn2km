<?php

namespace Tests\Feature;

use App\Exports\ServiceHistoryExport;
use App\Filament\Resources\ServiceHistoryResource;
use App\Filament\Resources\TicketResource;
use App\Filament\Resources\ServiceHistoryResource\Pages\ListServiceHistory;
use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\User;
use App\Services\TicketService;
use DateTimeInterface;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/** Riwayat Layanan & Audit Trail: a read-only list of every ticket step. */
class ServiceHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    /** History entries cannot be changed afterwards, so back-dated ones are written that way. */
    private function writeLog(array $attributes, ?DateTimeInterface $at = null): TicketLog
    {
        $log = new TicketLog();
        $log->forceFill($attributes + ($at ? ['created_at' => $at, 'updated_at' => $at] : []))->save();

        return $log;
    }

    private function logStep(): TicketLog
    {
        $ticket = Ticket::firstOrFail();

        return TicketLog::create([
            'ticket_id' => $ticket->id,
            'action' => 'status_changed',
            'performed_by' => $this->user('staff1@mtsn2malang.sch.id')->id,
            'from_status' => 'submitted',
            'to_status' => 'verified',
            'notes' => 'Berkas lengkap, lanjut proses.',
        ]);
    }

    public static function readers(): array
    {
        return [
            'back_office' => ['staff1@mtsn2malang.sch.id'],
            'supervisor' => ['pengawas@mtsn2malang.sch.id'],
        ];
    }

    /** @dataProvider readers */
    public function test_back_office_and_supervisors_can_read_the_history(string $email): void
    {
        $log = $this->logStep();

        $this->actingAs($this->user($email))
            ->get(ServiceHistoryResource::getUrl('index'))
            ->assertOk();

        Livewire::test(ListServiceHistory::class)
            ->assertCanSeeTableRecords([$log])
            ->assertSee('Diajukan → Diverifikasi')
            ->assertSee('Berkas lengkap, lanjut proses.');
    }

    public function test_front_desk_cannot_open_the_history(): void
    {
        $this->actingAs($this->user('loket1@mtsn2malang.sch.id'))
            ->get(ServiceHistoryResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_history_is_read_only(): void
    {
        $log = $this->logStep();
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'));

        $this->assertFalse(ServiceHistoryResource::canCreate());
        $this->assertFalse(ServiceHistoryResource::canEdit($log));
        $this->assertFalse(ServiceHistoryResource::canDelete($log));
        $this->assertFalse(ServiceHistoryResource::canDeleteAny());
    }

    public function test_ticket_page_shows_the_timeline_in_order(): void
    {
        $log = $this->logStep();
        $ticket = $log->ticket;
        TicketLog::create([
            'ticket_id' => $ticket->id,
            'action' => 'note_added',
            'performed_by' => $log->performed_by,
            'notes' => 'Menunggu tanda tangan kepala madrasah.',
        ]);

        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'))
            ->get(TicketResource::getUrl('view', ['record' => $ticket]))
            ->assertOk()
            ->assertSee('Riwayat layanan')
            ->assertSeeInOrder(['Berkas lengkap, lanjut proses.', 'Menunggu tanda tangan kepala madrasah.']);
    }

    public function test_log_detail_shows_actor_status_and_notes(): void
    {
        $log = $this->logStep();
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));

        Livewire::test(ListServiceHistory::class)
            ->mountTableAction('view', $log)
            ->assertSee('Detail log')
            ->assertSee('Diajukan → Diverifikasi')
            ->assertSee('Berkas lengkap, lanjut proses.')
            ->assertSee('Tidak ada berkas pada langkah ini.');
    }

    public function test_ticket_page_lists_dispositions_with_signature_model(): void
    {
        $ticket = Ticket::firstOrFail();
        $leader = $this->user('kepsek@mtsn2malang.sch.id');
        $ticket->service->update(['approval_required' => true, 'approval_roles' => null, 'approval_users' => null]);
        $ticket->update(['status' => 'verified', 'approval_required' => true, 'approval_status' => 'pending']);
        app(TicketService::class)->dispose($ticket->fresh(), $leader, 'tte_upload', [], null, 'Segera diproses.');

        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'))
            ->get(TicketResource::getUrl('view', ['record' => $ticket]))
            ->assertOk()
            ->assertSee('Riwayat disposisi & tanda tangan')
            ->assertSee($leader->name)
            ->assertSee('Didisposisi')
            ->assertSee('TTE — unduh, tanda tangani elektronik, lalu unggah');
    }

    public function test_ticket_page_lists_file_and_output_changes(): void
    {
        $ticket = Ticket::firstOrFail();
        $staff = $this->user('staff1@mtsn2malang.sch.id');
        foreach (['Hasil layanan surat-v1.pdf diunggah.', 'Hasil layanan surat-v2.pdf diunggah.'] as $i => $notes) {
            $this->writeLog([
                'ticket_id' => $ticket->id,
                'action' => 'output_uploaded',
                'performed_by' => $staff->id,
                'notes' => $notes,
            ], now()->subHours(2 - $i));
        }

        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'))
            ->get(TicketResource::getUrl('view', ['record' => $ticket]))
            ->assertOk()
            ->assertSee('Riwayat berkas & hasil layanan')
            ->assertSeeInOrder(['surat-v2.pdf', 'surat-v1.pdf', 'Diganti versi lebih baru']);
    }

    public function test_history_can_be_searched_and_filtered(): void
    {
        $statusLog = $this->logStep();
        $noteLog = $this->writeLog([
            'ticket_id' => $statusLog->ticket_id,
            'action' => 'note_added',
            'performed_by' => $this->user('kepsek@mtsn2malang.sch.id')->id,
            'notes' => 'Mohon dilengkapi fotokopi rapor.',
        ], now()->subDays(10));

        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));

        Livewire::test(ListServiceHistory::class)
            ->filterTable('action', ['note_added'])
            ->assertCanSeeTableRecords([$noteLog])
            ->assertCanNotSeeTableRecords([$statusLog]);

        Livewire::test(ListServiceHistory::class)
            ->filterTable('performed_by', $statusLog->performed_by)
            ->assertCanSeeTableRecords([$statusLog])
            ->assertCanNotSeeTableRecords([$noteLog]);

        Livewire::test(ListServiceHistory::class)
            ->filterTable('period', ['from' => now()->subDay()->toDateString(), 'until' => null])
            ->assertCanSeeTableRecords([$statusLog])
            ->assertCanNotSeeTableRecords([$noteLog]);

        Livewire::test(ListServiceHistory::class)
            ->searchTable('fotokopi rapor')
            ->assertCanSeeTableRecords([$noteLog])
            ->assertCanNotSeeTableRecords([$statusLog]);

        Livewire::test(ListServiceHistory::class)
            ->filterTable('service', $statusLog->ticket->service_id)
            ->assertCanSeeTableRecords([$statusLog, $noteLog]);
    }

    public function test_history_export_follows_the_active_filters(): void
    {
        Excel::fake();
        $this->freezeTime();
        $this->logStep();
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));

        Livewire::test(ListServiceHistory::class)
            ->filterTable('action', ['status_changed'])
            ->callAction('export');

        Excel::assertDownloaded('riwayat-layanan-' . now()->format('Ymd-His') . '.xlsx', function (ServiceHistoryExport $export) {
            $rows = $export->query()->get()->map(fn ($log) => $export->map($log));

            return $rows->isNotEmpty()
                && $rows->every(fn (array $row) => $row[3] === 'Status diubah')
                && $rows->contains(fn (array $row) => $row[7] === 'Berkas lengkap, lanjut proses.');
        });

        $this->assertDatabaseHas('activity_log', ['description' => 'Mengekspor riwayat layanan']);
    }

    public function test_status_changes_outside_the_service_are_still_recorded(): void
    {
        $ticket = Ticket::where('status', '!=', 'cancelled')->firstOrFail();
        $from = $ticket->status;
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'));

        $ticket->update(['status' => 'cancelled']);

        $this->assertDatabaseHas('ticket_logs', [
            'ticket_id' => $ticket->id,
            'action' => 'status_changed',
            'from_status' => $from,
            'to_status' => 'cancelled',
            'performed_by' => $this->user('ptsp@mtsn2malang.sch.id')->id,
        ]);
    }

    public function test_service_changes_are_recorded_once(): void
    {
        $ticket = Ticket::where('status', 'submitted')->first() ?? tap(Ticket::firstOrFail())->update(['status' => 'submitted']);
        $officer = $this->user('staff1@mtsn2malang.sch.id');
        $before = TicketLog::where('ticket_id', $ticket->id)->count();

        app(TicketService::class)->assign($ticket->fresh(), $officer, $officer);

        $logs = TicketLog::where('ticket_id', $ticket->id)->orderBy('id')->get()->slice($before);
        $this->assertCount(1, $logs);
        $this->assertSame('assigned', $logs->first()->action);
        $this->assertSame('in_process', $logs->first()->to_status);
    }

    public function test_history_endpoint_shows_each_reader_what_they_may_see(): void
    {
        $ticket = Ticket::whereHas('user', fn ($q) => $q->whereDoesntHave('roles', fn ($r) => $r->whereIn('name', User::STAFF_ROLES)))->firstOrFail();
        $this->writeLog([
            'ticket_id' => $ticket->id,
            'action' => 'status_changed',
            'performed_by' => $this->user('staff1@mtsn2malang.sch.id')->id,
            'from_status' => 'submitted',
            'to_status' => 'verified',
            'notes' => 'Berkas lengkap, lanjut proses.',
            'ip_address' => '10.0.0.7',
            'metadata' => ['file_name' => 'ijazah.pdf'],
        ]);
        $url = '/api/tiket/' . $ticket->ticket_number . '/riwayat';

        Sanctum::actingAs($ticket->user);
        $applicant = $this->getJson($url)->assertOk()->json('riwayat');
        $entry = collect($applicant)->firstWhere('catatan', 'Berkas lengkap, lanjut proses.');
        $this->assertSame('Diajukan → Diverifikasi', $entry['perubahan_status']);
        $this->assertArrayNotHasKey('pelaku', $entry);
        $this->assertArrayNotHasKey('ip', $entry);

        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'));
        $entry = collect($this->getJson($url)->json('riwayat'))->firstWhere('catatan', 'Berkas lengkap, lanjut proses.');
        $this->assertSame($this->user('staff1@mtsn2malang.sch.id')->name, $entry['pelaku']);
        $this->assertSame(['file_name' => 'ijazah.pdf'], $entry['detail']);
        $this->assertArrayNotHasKey('ip', $entry);

        Sanctum::actingAs($this->user('pengawas@mtsn2malang.sch.id'));
        $entry = collect($this->getJson($url)->json('riwayat'))->firstWhere('catatan', 'Berkas lengkap, lanjut proses.');
        $this->assertSame('10.0.0.7', $entry['ip']);
    }

    public function test_other_applicants_cannot_read_a_tickets_history(): void
    {
        $ticket = Ticket::firstOrFail();
        $stranger = User::where('email', 'budi.santoso@email.com')->firstOrFail();
        if ($ticket->user_id === $stranger->id) {
            $ticket = Ticket::where('user_id', '!=', $stranger->id)->firstOrFail();
        }

        Sanctum::actingAs($stranger);
        $this->getJson('/api/tiket/' . $ticket->ticket_number . '/riwayat')->assertForbidden();
    }

    public function test_document_endpoint_lists_uploads_and_replaced_outputs(): void
    {
        $ticket = Ticket::firstOrFail();
        $staff = $this->user('staff1@mtsn2malang.sch.id');
        foreach (['Hasil layanan v1.pdf diunggah.', 'Hasil layanan v2.pdf diunggah.'] as $i => $notes) {
            $this->writeLog(['ticket_id' => $ticket->id, 'action' => 'output_uploaded', 'performed_by' => $staff->id, 'notes' => $notes], now()->subHours(2 - $i));
        }

        Sanctum::actingAs($staff);
        $entries = $this->getJson('/api/tiket/' . $ticket->ticket_number . '/berkas')->assertOk()->json('berkas');

        $this->assertSame('Hasil layanan v2.pdf diunggah.', $entries[0]['keterangan']);
        $this->assertFalse($entries[0]['diganti']);
        $this->assertTrue(collect($entries)->firstWhere('keterangan', 'Hasil layanan v1.pdf diunggah.')['diganti']);
    }

    public function test_history_search_endpoint_filters_like_the_panel(): void
    {
        $statusLog = $this->logStep();
        $noteLog = $this->writeLog([
            'ticket_id' => $statusLog->ticket_id,
            'action' => 'note_added',
            'performed_by' => $this->user('kepsek@mtsn2malang.sch.id')->id,
            'notes' => 'Mohon dilengkapi fotokopi rapor 100%.',
        ], now()->subDays(10));

        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'));
        $notes = fn (string $query) => collect($this->getJson('/api/riwayat?' . $query)->assertOk()->json('data'))->pluck('catatan');

        $this->assertTrue($notes('kegiatan=note_added')->contains('Mohon dilengkapi fotokopi rapor 100%.'));
        $this->assertFalse($notes('kegiatan=note_added')->contains('Berkas lengkap, lanjut proses.'));
        $this->assertSame(['Mohon dilengkapi fotokopi rapor 100%.'], $notes('q=' . urlencode('rapor 100%'))->all());
        $this->assertFalse($notes('dari=' . now()->subDay()->toDateString())->contains('Mohon dilengkapi fotokopi rapor 100%.'));
        $this->assertTrue($notes('pelaku=' . $statusLog->performed_by . '&status=verified')->contains('Berkas lengkap, lanjut proses.'));
        $this->getJson('/api/riwayat?kegiatan=hapus')->assertUnprocessable();

        Sanctum::actingAs($this->user('loket1@mtsn2malang.sch.id'));
        $this->getJson('/api/riwayat')->assertForbidden();
    }

    public function test_history_export_endpoint_downloads_the_filtered_rows(): void
    {
        Excel::fake();
        $this->freezeTime();
        $this->logStep();
        Sanctum::actingAs($this->user('pengawas@mtsn2malang.sch.id'));

        $this->get('/api/riwayat/ekspor?kegiatan=status_changed')->assertOk();

        Excel::assertDownloaded('riwayat-layanan-' . now()->format('Ymd-His') . '.xlsx', fn (ServiceHistoryExport $export) => $export->query()->get()->every(fn ($log) => $log->action === 'status_changed'));
        $this->assertDatabaseHas('activity_log', ['description' => 'Mengekspor riwayat layanan']);
    }

    public function test_history_entries_cannot_be_changed_or_removed(): void
    {
        $log = $this->logStep();

        try {
            $log->update(['notes' => 'diubah']);
            $this->fail('The model allowed an update.');
        } catch (\LogicException) {
        }

        try {
            $log->delete();
            $this->fail('The model allowed a delete.');
        } catch (\LogicException) {
        }

        // Bypassing the model is refused by the database trigger.
        foreach ([
            fn () => DB::table('ticket_logs')->where('id', $log->id)->update(['notes' => 'diubah']),
            fn () => DB::table('ticket_logs')->where('id', $log->id)->delete(),
        ] as $attempt) {
            try {
                DB::transaction($attempt);
                $this->fail('The database allowed changing history.');
            } catch (QueryException $e) {
                $this->assertStringContainsString('Riwayat layanan tidak dapat diubah atau dihapus', $e->getMessage());
            }
        }

        $this->assertSame('Berkas lengkap, lanjut proses.', $log->fresh()->notes);
    }

    public function test_deleting_the_actor_keeps_their_history(): void
    {
        $log = $this->logStep();
        $actor = User::findOrFail($log->performed_by);

        $actor->forceDelete();

        $this->assertNull($log->fresh()->performed_by);
        $this->assertSame('Berkas lengkap, lanjut proses.', $log->fresh()->notes);
    }
}
