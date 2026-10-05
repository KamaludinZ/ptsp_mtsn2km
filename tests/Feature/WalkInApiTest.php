<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Pendaftaran walk-in di loket through the API. */
class WalkInApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        Storage::fake('local');
        $this->seed();
    }

    public function test_front_desk_registers_a_walk_in_request_with_documents(): void
    {
        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());
        $service = Service::requestable('umum', 'offline')->availableFor('umum')->firstOrFail();

        $response = $this->postJson('/api/loket/permohonan', [
            'nama' => 'Pak Budi',
            'kategori' => 'umum',
            'whatsapp' => '6281200001111',
            'layanan' => $service->slug,
            'keterangan' => 'Legalisir ijazah 3 lembar',
            'berkas' => [UploadedFile::fake()->create('ijazah.pdf', 50, 'application/pdf')],
        ])->assertCreated()->assertJsonPath('pemohon', 'Pak Budi');

        $ticket = Ticket::where('ticket_number', $response->json('nomor_tiket'))->firstOrFail();
        $this->assertSame('offline', $ticket->mode);
        $this->assertSame($service->id, $ticket->service_id);
        $this->assertSame('6281200001111', $ticket->user->whatsapp_number);
        $this->assertDatabaseHas('ticket_files', ['ticket_id' => $ticket->id, 'file_name' => 'ijazah.pdf']);
    }

    public function test_request_is_validated_and_limited_to_the_front_desk(): void
    {
        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());
        $this->postJson('/api/loket/permohonan', ['nama' => 'X', 'kategori' => 'umum', 'whatsapp' => '1', 'layanan' => 'tidak-ada', 'keterangan' => 'x'])
            ->assertUnprocessable()->assertJsonValidationErrors('layanan');
        $this->postJson('/api/loket/permohonan', [])->assertJsonValidationErrors(['nama', 'kategori', 'whatsapp', 'layanan', 'keterangan']);

        Sanctum::actingAs(User::where('email', 'kepsek@mtsn2malang.sch.id')->firstOrFail());
        $this->postJson('/api/loket/permohonan', [])->assertForbidden();
    }
}
