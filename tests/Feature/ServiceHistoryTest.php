<?php

namespace Tests\Feature;

use App\Exports\ServiceHistoryExport;
use App\Filament\Resources\ServiceHistoryResource;
use App\Filament\Resources\TicketResource;
use App\Filament\Resources\ServiceHistoryResource\Pages\ListServiceHistory;
use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $ticket->update(['approval_required' => true, 'signature_type' => 'tte']);
        TicketLog::create([
            'ticket_id' => $ticket->id,
            'action' => 'approved',
            'performed_by' => $leader->id,
            'from_status' => 'in_process',
            'to_status' => 'approved',
            'notes' => 'Disetujui pimpinan (TTE). Segera diproses.',
        ]);

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
            TicketLog::create([
                'ticket_id' => $ticket->id,
                'action' => 'output_uploaded',
                'performed_by' => $staff->id,
                'notes' => $notes,
            ])->forceFill(['created_at' => now()->subHours(2 - $i)])->save();
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
        $noteLog = TicketLog::create([
            'ticket_id' => $statusLog->ticket_id,
            'action' => 'note_added',
            'performed_by' => $this->user('kepsek@mtsn2malang.sch.id')->id,
            'notes' => 'Mohon dilengkapi fotokopi rapor.',
        ]);
        $noteLog->forceFill(['created_at' => now()->subDays(10)])->save();

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
}
