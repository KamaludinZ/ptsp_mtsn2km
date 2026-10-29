<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketOwnerVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** TicketOwnerVerifier: the applicant's e-mail or WhatsApp number proves the ticket is theirs. */
class TicketOwnerVerifierTest extends TestCase
{
    use RefreshDatabase;

    private function ticketOf(array $applicant): Ticket
    {
        return Ticket::factory()->create(['user_id' => User::factory()->create($applicant)->id]);
    }

    public function test_the_applicant_s_email_or_number_written_any_way_matches(): void
    {
        $ticket = $this->ticketOf(['email' => 'Siti.Aminah@Contoh.id', 'whatsapp_number' => '0812-3450-0001']);
        $verifier = app(TicketOwnerVerifier::class);

        foreach (['siti.aminah@contoh.id', '  SITI.AMINAH@CONTOH.ID ', '081234500001', '+62 812 3450 0001', '6281234500001', '0062 812-3450-0001'] as $contact) {
            $this->assertTrue($verifier->matches($ticket, $contact), $contact);
        }
        foreach (['siti@contoh.id', '081234500002', '001', '', null, 'siti.aminah@contoh.id.evil'] as $contact) {
            $this->assertFalse($verifier->matches($ticket, $contact), (string) $contact);
        }
    }

    public function test_the_counter_s_made_up_email_is_no_proof_but_the_number_is(): void
    {
        $ticket = $this->ticketOf(['email' => '081234500003@walkin.local', 'whatsapp_number' => '081234500003']);
        $verifier = app(TicketOwnerVerifier::class);

        $this->assertFalse($verifier->matches($ticket, '081234500003@walkin.local'));
        $this->assertTrue($verifier->matches($ticket, '081234500003'));
    }

    public function test_the_contact_the_ticket_recorded_counts_too(): void
    {
        // An older request kept the number it was made with; the applicant changed theirs since.
        $ticket = $this->ticketOf(['email' => '081234500005@walkin.local', 'whatsapp_number' => '081299990005']);
        $ticket->forceFill(['whatsapp_number' => '0812-3450-0005', 'email' => 'Lama@Contoh.id'])->saveQuietly();
        $verifier = app(TicketOwnerVerifier::class);

        $this->assertTrue($verifier->matches($ticket, '+62 812 3450 0005')); // on the ticket
        $this->assertTrue($verifier->matches($ticket, '081299990005')); // on the account now
        $this->assertTrue($verifier->matches($ticket, 'lama@contoh.id'));
        $this->assertFalse($verifier->matches($ticket, '081234500006'));
    }

    public function test_an_applicant_without_a_number_proves_it_by_email_only(): void
    {
        $ticket = $this->ticketOf(['email' => 'budi@contoh.id', 'whatsapp_number' => null]);

        $this->assertFalse(app(TicketOwnerVerifier::class)->matches($ticket, '081234500004'));
        $this->assertTrue(app(TicketOwnerVerifier::class)->matches($ticket, 'budi@contoh.id'));
    }
}
