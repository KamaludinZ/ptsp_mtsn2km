<?php

namespace Tests\Feature;

use App\Models\PersuratanMaster;
use App\Models\Ticket;
use App\Models\User;
use App\Services\JenisSkService;
use App\Support\Persuratan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** GET /api/penomoran-sk/registrasi, then POST /api/penomoran-sk: Registrasi Nomor SK for API clients. */
class RegisterSkRegistrationApiTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    private Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $this->ticket = Ticket::with(['user', 'service'])->whereHas('user')->whereHas('service')->firstOrFail();
    }

    public function test_a_blank_registration_shows_the_number_the_date_would_get(): void
    {
        Sanctum::actingAs($this->officer, ['*']);
        $year = now()->year;

        $this->getJson('/api/penomoran-sk/registrasi')->assertOk()
            ->assertJsonPath('isian', ['tanggal_surat' => now()->toDateString(), 'jenis_sk' => null, 'nama_peminta' => null, 'keterangan' => null, 'tiket' => null])
            ->assertJsonPath('nomor_berikutnya', ['nomor_urut' => 7, 'nomor_sk' => "7 TAHUN {$year}", 'tahun' => $year])
            ->assertJsonPath('catatan_tahun', null)
            ->assertJsonPath('perihal_mirip', [])
            ->assertJsonPath('petugas', $this->officer->name);

        $this->getJson('/api/penomoran-sk/registrasi?tanggal_surat=' . ($year - 1) . '-12-30')->assertOk()
            ->assertJsonPath('nomor_berikutnya.nomor_sk', '1 TAHUN ' . ($year - 1))
            ->assertJsonPath('catatan_tahun', 'Tahun ' . ($year - 1) . ' sudah lewat: nomor ini menyambung register SK tahun ' . ($year - 1) . '. Pastikan tanggal surat memang tahun ' . ($year - 1) . '.');

        $this->getJson('/api/penomoran-sk/registrasi?perihal=' . urlencode('Panitia penerimaan peserta didik baru gelombang 2'))->assertOk()
            ->assertJsonPath('perihal_mirip.0.nomor_sk', "3 TAHUN {$year}");
    }

    public function test_from_a_ticket_the_form_starts_filled_in_and_the_number_is_the_one_shown(): void
    {
        $tugas = PersuratanMaster::where('type', 'jenis_sk')->where('nama', Persuratan::JENIS_SK[1])->firstOrFail();
        JenisSkService::assignServices($tugas, [$this->ticket->service_id]);
        Sanctum::actingAs($this->officer, ['*']);

        $draft = $this->getJson('/api/penomoran-sk/registrasi?tiket=' . $this->ticket->id)->assertOk()
            ->assertJsonPath('isian.jenis_sk', $tugas->nama)
            ->assertJsonPath('isian.nama_peminta', $this->ticket->user->name)
            ->assertJsonPath('isian.keterangan', 'Untuk permohonan ' . $this->ticket->ticket_number . ' (' . $this->ticket->service->name . ').')
            ->assertJsonPath('isian.tiket', $this->ticket->id)
            ->json();

        $this->withHeader('Idempotency-Key', 'registrasi-' . $this->ticket->id)
            ->postJson('/api/penomoran-sk', [...$draft['isian'], 'perihal' => 'Penugasan guru pendamping'])->assertCreated()
            ->assertJsonPath('nomor_sk', $draft['nomor_berikutnya']['nomor_sk'])
            ->assertJsonPath('tiket.id', $this->ticket->id)
            ->assertJsonPath('jenis_sk', $tugas->nama);
    }

    public function test_only_a_token_that_may_take_numbers_opens_a_registration(): void
    {
        Sanctum::actingAs($this->officer, ['penomoran-sk:baca']);
        $this->getJson('/api/penomoran-sk/registrasi')->assertForbidden();

        Sanctum::actingAs($this->officer, ['penomoran-sk:tulis']);
        $this->getJson('/api/penomoran-sk/registrasi?tiket=999999')->assertJsonValidationErrors(['tiket' => 'Permohonan (tiket) tidak ditemukan.']);
        $this->getJson('/api/penomoran-sk/registrasi?tanggal_surat=bukan-tanggal')->assertJsonValidationErrors('tanggal_surat');

        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail(), ['*']);
        $this->getJson('/api/penomoran-sk/registrasi')->assertForbidden();
    }
}
