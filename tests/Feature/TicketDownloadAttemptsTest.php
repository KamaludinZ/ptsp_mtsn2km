<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\TicketOutput;
use App\Models\User;
use App\Services\TicketDownloadAttempts;
use App\Support\TicketTracking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Batas percobaan & kunci: downloading a finished request's product from public tracking (POST /tracking/unduh). */
class TicketDownloadAttemptsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function finished(array $applicant = [], array $ticket = []): Ticket
    {
        $user = User::factory()->create($applicant + ['email' => 'siti.aminah@contoh.id', 'whatsapp_number' => '081234500001']);
        $ticket = Ticket::factory()->create($ticket + ['user_id' => $user->id, 'status' => 'completed']);
        Storage::disk('local')->put('ticket-outputs/sk-' . $ticket->id . '.pdf', '%PDF-1.4 hasil');
        TicketOutput::create(['ticket_id' => $ticket->id, 'output_type' => 'digital', 'file_path' => 'ticket-outputs/sk-' . $ticket->id . '.pdf']);

        return $ticket->fresh();
    }

    private function verify(Ticket $ticket, string $contact, string $ip = '10.0.0.1')
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->postJson(route('onlineportal.track.ticket.download'), ['ticket_number' => $ticket->ticket_number, 'kontak' => $contact]);
    }

    public function test_the_applicant_s_contact_gives_a_short_lived_link_to_the_file(): void
    {
        $ticket = $this->finished();

        $link = $this->verify($ticket, '+62 812-3450-0001')->assertOk()->json('unduh');
        $this->get($link)->assertOk()->assertDownload('hasil-layanan-' . $ticket->ticket_number . '.pdf');

        // The link is signed: changed or expired, it does not open.
        $this->get(str_replace('signature=', 'signature=0', $link))->assertForbidden();
        $this->get(route('onlineportal.track.ticket.output', ['output' => $ticket->output]))->assertForbidden();
        $this->travel(TicketTracking::LINK_MINUTES + 1)->minutes();
        $this->get($link)->assertForbidden();
    }

    public function test_a_wrong_contact_counts_and_the_third_locks_the_ticket(): void
    {
        $ticket = $this->finished();

        $this->verify($ticket, 'orang.lain@contoh.id')->assertStatus(422)
            ->assertJsonPath('sisa_percobaan', 2)
            ->assertJsonPath('message', 'Email atau nomor WhatsApp tidak cocok dengan data pemohon tiket ini.');
        $this->verify($ticket, '081299999999')->assertStatus(422)->assertJsonPath('sisa_percobaan', 1);

        $locked = $this->verify($ticket, '081299999998')->assertStatus(429)->assertHeader('Retry-After');
        $this->assertSame(TicketDownloadAttempts::LOCK_MINUTES * 60, $locked->json('coba_lagi_detik'));
        $this->assertStringContainsString('dikunci sementara', $locked->json('message'));

        // Locked for everyone, the right contact included: it is not even compared.
        $this->verify($ticket, 'siti.aminah@contoh.id', '10.0.0.2')->assertStatus(429)->assertJsonMissingPath('unduh');

        $this->travel(TicketDownloadAttempts::LOCK_MINUTES)->minutes();
        $this->travel(1)->seconds();
        $this->verify($ticket, 'orang.lain@contoh.id')->assertStatus(422)->assertJsonPath('sisa_percobaan', 2); // a fresh count
        $this->verify($ticket, 'siti.aminah@contoh.id')->assertOk()->assertJsonStructure(['unduh']);
    }

    public function test_the_right_contact_clears_the_count(): void
    {
        $ticket = $this->finished();

        $this->verify($ticket, 'orang.lain@contoh.id')->assertStatus(422)->assertJsonPath('sisa_percobaan', 2);
        $this->verify($ticket, '081299999999')->assertStatus(422)->assertJsonPath('sisa_percobaan', 1);
        $this->verify($ticket, '0812 3450 0001')->assertOk();

        $this->verify($ticket, 'orang.lain@contoh.id')->assertStatus(422)->assertJsonPath('sisa_percobaan', 2);
    }

    public function test_one_address_gets_only_so_many_wrong_tries_over_all_tickets(): void
    {
        $tickets = collect(range(1, 5))->map(fn (int $i) => $this->finished(['email' => "pemohon{$i}@contoh.id", 'whatsapp_number' => '08123450000' . $i]));

        // Two wrong tries on each ticket lock none of them, but the tenth locks the address.
        foreach ($tickets->values() as $i => $ticket) {
            $this->verify($ticket, 'tebakan@contoh.id')->assertStatus(422);
            $this->verify($ticket, 'tebakan.lain@contoh.id')->assertStatus($i < 4 ? 422 : 429);
        }
        $this->travel(2)->minutes(); // past the route's per-minute throttle
        $locked = $this->verify($tickets->first(), 'pemohon1@contoh.id')->assertStatus(429);
        $this->assertLessThanOrEqual(3600, $locked->json('coba_lagi_detik'));
        $this->assertGreaterThan(TicketDownloadAttempts::LOCK_MINUTES * 60, $locked->json('coba_lagi_detik'));

        // Another address may still try, and the applicant gets in.
        $this->verify($tickets->first(), 'pemohon1@contoh.id', '10.0.0.9')->assertOk();
    }

    public function test_tries_sent_at_once_cannot_slip_past_the_limit(): void
    {
        $ticket = $this->finished();
        $attempts = app(TicketDownloadAttempts::class);

        // Two tries counted, a third already on its way (counted, not yet answered).
        $attempts->attempt($ticket, 'a@contoh.id', '10.0.0.1');
        $attempts->attempt($ticket, 'b@contoh.id', '10.0.0.1');
        RateLimiter::increment('unduh-hasil:' . $ticket->id, TicketDownloadAttempts::LOCK_MINUTES * 60);

        // A fourth, even the right one, is not compared.
        $try = $attempts->attempt($ticket, 'siti.aminah@contoh.id', '10.0.0.3');
        $this->assertFalse($try['matched']);
        $this->assertSame(TicketDownloadAttempts::LOCK_MINUTES * 60, $try['locked']);
    }

    public function test_only_a_finished_request_s_current_file_is_given(): void
    {
        $ticket = $this->finished();
        $link = $this->verify($ticket, 'siti.aminah@contoh.id')->assertOk()->json('unduh');

        // Not finished (any more): nothing to verify for, and the link no longer opens.
        $ticket->forceFill(['status' => 'in_process'])->saveQuietly();
        $this->verify($ticket, 'siti.aminah@contoh.id')->assertNotFound()->assertJsonPath('message', 'Hasil layanan tiket ini belum dapat diunduh.');
        $this->get($link)->assertNotFound();
        $ticket->forceFill(['status' => 'completed'])->saveQuietly();

        // A newer product replaces the old one: the old link is gone.
        Storage::disk('local')->put('ticket-outputs/sk-baru.pdf', '%PDF-1.4 baru');
        TicketOutput::create(['ticket_id' => $ticket->id, 'output_type' => 'digital', 'file_path' => 'ticket-outputs/sk-baru.pdf']);
        $this->get($link)->assertNotFound();

        // An unknown number is not counted against anyone.
        $this->verify(new Ticket(['ticket_number' => 'TKT-TIDAK-ADA']), 'x@contoh.id')->assertNotFound();
        $this->assertSame(0, RateLimiter::attempts('unduh-hasil-jaringan:10.0.0.1'));
    }

    public function test_the_ticket_number_and_contact_are_required(): void
    {
        $this->postJson(route('onlineportal.track.ticket.download'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ticket_number', 'kontak'])
            ->assertJsonMissingPath('sisa_percobaan');
    }
}
