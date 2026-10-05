<?php

namespace Tests\Feature;

use App\Filament\Portal\Resources\TicketResource\Pages\ViewTicket as PortalViewTicket;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

/** Portal pemohon API: applicants see only their own requests. */
class ApplicantApiTest extends TestCase
{
    use RefreshDatabase;

    private User $applicant;

    private Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();

        $this->applicant = tap(User::factory()->create(['user_type' => 'umum']))->assignRole('umum');
        $service = Service::availableFor('umum')->firstOrFail();
        $this->ticket = app(TicketService::class)->open($service, $this->applicant, $this->applicant, 'online', 'Legalisir ijazah 3 lembar');
    }

    public function test_applicant_lists_and_reads_own_requests(): void
    {
        Sanctum::actingAs($this->applicant);

        $this->getJson('/api/permohonan')->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.nomor_tiket', $this->ticket->ticket_number);
        $this->getJson('/api/permohonan?status=completed')->assertOk()->assertJsonPath('total', 0);

        $this->getJson('/api/permohonan/' . $this->ticket->ticket_number)
            ->assertOk()
            ->assertJsonPath('keterangan', 'Legalisir ijazah 3 lembar')
            ->assertJsonPath('riwayat.0.kegiatan', 'Permohonan dibuat')
            ->assertJsonPath('hasil', null);
    }

    public function test_other_applicants_cannot_read_it(): void
    {
        Sanctum::actingAs(tap(User::factory()->create())->assignRole('umum'));

        $this->getJson('/api/permohonan')->assertOk()->assertJsonPath('total', 0);
        $this->getJson('/api/permohonan/' . $this->ticket->ticket_number)->assertNotFound();
    }

    public function test_applicant_submits_a_request_with_documents(): void
    {
        Storage::fake('local');
        Sanctum::actingAs($this->applicant);
        $service = Service::availableFor('umum')->firstOrFail();

        $number = $this->post('/api/permohonan', [
            'layanan' => $service->slug,
            'keterangan' => 'Surat keterangan aktif',
            'berkas' => [UploadedFile::fake()->create('ktp.pdf', 30, 'application/pdf')],
        ], ['Accept' => 'application/json'])->assertCreated()->json('nomor_tiket');

        $ticket = Ticket::where('ticket_number', $number)->firstOrFail();
        $this->assertSame($this->applicant->id, $ticket->user_id);
        $this->assertSame('ktp.pdf', $ticket->files()->first()->file_name);
        Storage::disk('local')->assertExists($ticket->files()->first()->file_path);

        $this->postJson('/api/permohonan', ['layanan' => 'tidak-ada', 'keterangan' => 'x'])->assertUnprocessable()->assertJsonValidationErrors('layanan');
        $this->post('/api/permohonan', ['layanan' => $service->slug, 'keterangan' => 'x', 'berkas' => [UploadedFile::fake()->create('a.exe', 5)]], ['Accept' => 'application/json'])
            ->assertUnprocessable()->assertJsonValidationErrors('berkas.0');
    }

    public function test_staff_cannot_apply_through_the_portal_api(): void
    {
        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $this->postJson('/api/permohonan', ['layanan' => 'x', 'keterangan' => 'x'])->assertForbidden();
    }

    public function test_applicant_adds_missing_documents_while_the_request_is_open(): void
    {
        Storage::fake('local');
        Sanctum::actingAs($this->applicant);
        $url = '/api/permohonan/' . $this->ticket->ticket_number . '/berkas';

        $this->post($url, ['berkas' => [UploadedFile::fake()->create('kk.pdf', 20, 'application/pdf')]], ['Accept' => 'application/json'])
            ->assertCreated()->assertJsonPath('ditambahkan', ['kk.pdf']);
        $this->assertSame(['kk.pdf'], $this->ticket->files()->pluck('file_name')->all());

        $this->ticket->update(['status' => 'completed']);
        $this->post($url, ['berkas' => [UploadedFile::fake()->create('telat.pdf', 20, 'application/pdf')]], ['Accept' => 'application/json'])
            ->assertUnprocessable();

        Sanctum::actingAs(tap(User::factory()->create())->assignRole('umum'));
        $this->post($url, ['berkas' => [UploadedFile::fake()->create('x.pdf', 5, 'application/pdf')]], ['Accept' => 'application/json'])->assertNotFound();
    }

    public function test_applicant_never_sees_internal_notes(): void
    {
        app(TicketService::class)->addNote($this->ticket, 'Catatan internal petugas', User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        Sanctum::actingAs($this->applicant);

        $this->assertStringNotContainsString('Catatan internal petugas', $this->getJson('/api/permohonan/' . $this->ticket->ticket_number)->getContent());
    }

    public function test_applicant_sees_follow_up_messages_meant_for_them(): void
    {
        $officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        app(TicketService::class)->addNote($this->ticket, 'Mohon unggah fotokopi KK.', $officer, 'request_documents', true);
        app(TicketService::class)->addNote($this->ticket, 'Rahasia petugas.', $officer, 'internal');
        Sanctum::actingAs($this->applicant);

        $content = $this->getJson('/api/permohonan/' . $this->ticket->ticket_number)->assertOk()->getContent();
        $this->assertStringContainsString('Mohon unggah fotokopi KK.', $content);
        $this->assertStringNotContainsString('Rahasia petugas.', $content);

        $this->actingAs($this->applicant);
        Filament::setCurrentPanel(Filament::getPanel('portal'));
        Livewire::test(PortalViewTicket::class, ['record' => $this->ticket->getRouteKey()])
            ->assertSee('Pesan untuk pemohon')
            ->assertSee('Mohon unggah fotokopi KK.')
            ->assertDontSee('Rahasia petugas.');
    }

    public function test_portal_lets_the_applicant_add_documents(): void
    {
        Storage::fake('local');
        $this->actingAs($this->applicant);
        Filament::setCurrentPanel(Filament::getPanel('portal'));

        Livewire::test(PortalViewTicket::class, ['record' => $this->ticket->getRouteKey()])
            ->callAction('upload', ['files' => [UploadedFile::fake()->create('akta.pdf', 20, 'application/pdf')]])
            ->assertHasNoActionErrors()
            ->assertNotified('Berkas ditambahkan');

        $this->assertSame(1, $this->ticket->files()->count());
    }
}
