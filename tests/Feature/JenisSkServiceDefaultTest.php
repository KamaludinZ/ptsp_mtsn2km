<?php

namespace Tests\Feature;

use App\Filament\Resources\PersuratanMasterResource\Pages\ListPersuratanMasters;
use App\Filament\Resources\SuratKeputusanResource\Pages\RegisterSkNumber;
use App\Models\PersuratanMaster;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Services\JenisSkService;
use App\Support\Persuratan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/** Jenis SK bawaan per layanan PTSP: kept on Master Jenis SK, used when an SK is asked for from a ticket. */
class JenisSkServiceDefaultTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    private PersuratanMaster $tugas;

    private Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $this->tugas = PersuratanMaster::where('type', 'jenis_sk')->where('nama', Persuratan::JENIS_SK[1])->firstOrFail();
        $this->ticket = Ticket::with('service')->whereHas('service')->firstOrFail();
        $this->actingAs($this->officer);
    }

    public function test_assigning_services_sets_and_clears_their_default_and_is_logged(): void
    {
        $other = Service::whereKeyNot($this->ticket->service_id)->firstOrFail();

        JenisSkService::assignServices($this->tugas, [$this->ticket->service_id, $other->id]);
        $this->assertEqualsCanonicalizing([$this->ticket->service_id, $other->id], $this->tugas->layanan()->pluck('id')->all());

        JenisSkService::assignServices($this->tugas, [$other->id]);
        $this->assertNull($this->ticket->service->fresh()->jenis_sk_id);
        $this->assertSame([$other->name], $this->tugas->layanan()->pluck('name')->all());

        $log = Activity::inLog('jenis_sk')->latest('id')->firstOrFail();
        $this->assertSame('Mengatur layanan PTSP jenis SK ' . $this->tugas->nama, $log->description);
        $this->assertSame([$other->name], $log->properties['layanan']);
    }

    public function test_an_sk_asked_for_from_the_ticket_starts_with_the_service_jenis(): void
    {
        JenisSkService::assignServices($this->tugas, [$this->ticket->service_id]);

        Livewire::withQueryParams(['tiket' => $this->ticket->id])->test(RegisterSkNumber::class)
            ->assertFormSet(['jenis_sk' => $this->tugas->nama]);

        // Switched off, the jenis is no longer filled in.
        $this->tugas->update(['is_active' => false]);
        Livewire::withQueryParams(['tiket' => $this->ticket->id])->test(RegisterSkNumber::class)
            ->assertFormSet(['jenis_sk' => null]);
    }

    public function test_the_master_form_keeps_the_services_of_a_jenis(): void
    {
        Livewire::test(ListPersuratanMasters::class)
            ->set('activeTab', ListPersuratanMasters::JENIS_SK_TAB)
            ->callTableAction('edit', $this->tugas, ['layanan' => [$this->ticket->service_id]])
            ->assertHasNoTableActionErrors();

        $this->assertSame($this->tugas->id, $this->ticket->service->fresh()->jenis_sk_id);

        Livewire::test(ListPersuratanMasters::class)
            ->set('activeTab', ListPersuratanMasters::JENIS_SK_TAB)
            ->mountTableAction('edit', $this->tugas)
            ->assertTableActionDataSet(['layanan' => [$this->ticket->service_id]]);
    }

    public function test_the_api_reads_and_sets_the_services_of_a_jenis(): void
    {
        Sanctum::actingAs($this->officer, ['*']);

        $this->putJson("/api/persuratan/jenis-sk/{$this->tugas->id}", ['layanan' => [$this->ticket->service_id]])->assertOk()
            ->assertJsonPath('layanan', [['id' => $this->ticket->service_id, 'nama' => $this->ticket->service->name]]);
        // Left out, the services stay; an empty list clears them.
        $this->putJson("/api/persuratan/jenis-sk/{$this->tugas->id}", ['urutan' => 3])->assertOk()->assertJsonCount(1, 'layanan');
        $this->putJson("/api/persuratan/jenis-sk/{$this->tugas->id}", ['layanan' => []])->assertOk()->assertJsonPath('layanan', []);
        $this->putJson("/api/persuratan/jenis-sk/{$this->tugas->id}", ['layanan' => [999999]])->assertJsonValidationErrors(['layanan.0' => 'Layanan PTSP tidak ditemukan.']);
    }

    public function test_api_clients_find_the_jenis_sk_of_a_service(): void
    {
        JenisSkService::assignServices($this->tugas, [$this->ticket->service_id]);
        Sanctum::actingAs($this->officer, ['penomoran-sk:baca']);

        $this->getJson('/api/persuratan/jenis-sk?layanan=' . $this->ticket->service_id)->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.nama', $this->tugas->nama);
        $other = Service::whereKeyNot($this->ticket->service_id)->firstOrFail();
        $this->getJson('/api/persuratan/jenis-sk?layanan=' . $other->id)->assertOk()->assertJsonPath('total', 0);
        $this->getJson('/api/persuratan/jenis-sk?layanan=999999')->assertJsonValidationErrors(['layanan' => 'Layanan PTSP tidak ditemukan.']);
    }

    public function test_deleting_a_jenis_leaves_its_services_without_a_default(): void
    {
        $jenis = PersuratanMaster::create(['type' => 'jenis_sk', 'nama' => 'SK Penghargaan', 'is_active' => true, 'sort' => 9]);
        JenisSkService::assignServices($jenis, [$this->ticket->service_id]);

        $jenis->delete();

        $this->assertNull($this->ticket->service->fresh()->jenis_sk_id);
    }
}
