<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\TicketOutput;
use App\Models\User;
use App\Services\TicketDownloadAttempts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The product file through the API: POST /api/publik/lacak/{nomor}/unduh for an
 * applicant without an account, GET /api/permohonan/{nomor}/hasil for one
 * signed in.
 */
class TicketOutputDownloadApiTest extends TestCase
{
    use RefreshDatabase;

    private Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $user = User::factory()->create(['email' => 'siti.aminah@contoh.id', 'whatsapp_number' => '081234500001']);
        $this->ticket = Ticket::factory()->create(['user_id' => $user->id, 'status' => 'completed']);
        Storage::disk('local')->put('ticket-outputs/acak123.pdf', '%PDF-1.4 hasil');
        TicketOutput::create(['ticket_id' => $this->ticket->id, 'output_type' => 'digital', 'file_path' => 'ticket-outputs/acak123.pdf']);
    }

    private function verify(string $contact, ?string $number = null)
    {
        return $this->postJson(route('api.publik.lacak.unduh', ['nomor' => $number ?? $this->ticket->ticket_number]), ['kontak' => $contact]);
    }

    public function test_the_public_api_gives_the_verified_applicant_a_signed_link(): void
    {
        $link = $this->verify('0812-3450-0001')->assertOk()->json('unduh');

        $this->get($link)->assertOk()
            ->assertDownload('hasil-layanan-' . $this->ticket->ticket_number . '.pdf')
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_the_public_api_counts_with_the_tracking_page(): void
    {
        $this->verify('orang.lain@contoh.id')->assertStatus(422)->assertJsonPath('sisa_percobaan', 2);
        $this->postJson(route('onlineportal.track.ticket.download'), ['ticket_number' => $this->ticket->ticket_number, 'kontak' => '081299999999'])
            ->assertStatus(422)->assertJsonPath('sisa_percobaan', 1);

        $this->verify('081299999998')->assertStatus(429)->assertJsonPath('coba_lagi_detik', TicketDownloadAttempts::LOCK_MINUTES * 60);
        $this->postJson(route('onlineportal.track.ticket.download'), ['ticket_number' => $this->ticket->ticket_number, 'kontak' => 'siti.aminah@contoh.id'])->assertStatus(429);
    }

    public function test_the_public_api_refuses_what_it_cannot_check(): void
    {
        $this->verify('')->assertStatus(422)->assertJsonValidationErrors('kontak');
        $this->verify('siti.aminah@contoh.id', 'TKT-TIDAK-ADA')->assertNotFound();

        $this->ticket->forceFill(['status' => 'in_process'])->saveQuietly();
        $this->verify('siti.aminah@contoh.id')->assertNotFound()->assertJsonPath('message', 'Hasil layanan tiket ini belum dapat diunduh.');
    }

    public function test_the_signed_in_applicant_downloads_without_further_proof(): void
    {
        Sanctum::actingAs($this->ticket->user);

        $this->get(route('api.permohonan.hasil', $this->ticket))->assertOk()
            ->assertDownload('hasil-layanan-' . $this->ticket->ticket_number . '.pdf')
            ->assertHeader('Cache-Control', 'no-store, private');
        $preview = $this->get(route('api.permohonan.hasil', $this->ticket) . '?pratinjau=1')->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('inline', $preview->headers->get('Content-Disposition'));
    }

    public function test_only_the_applicant_and_only_a_file_on_the_disk(): void
    {
        $this->getJson(route('api.permohonan.hasil', $this->ticket))->assertUnauthorized();

        Sanctum::actingAs(User::factory()->create());
        $this->getJson(route('api.permohonan.hasil', $this->ticket))->assertNotFound();

        Sanctum::actingAs($this->ticket->user);
        Storage::disk('local')->delete('ticket-outputs/acak123.pdf');
        $this->getJson(route('api.permohonan.hasil', $this->ticket))->assertNotFound();
    }
}
