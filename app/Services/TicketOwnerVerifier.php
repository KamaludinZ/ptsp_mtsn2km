<?php

namespace App\Services;

use App\Models\Ticket;
use App\Support\Applicants;

/**
 * Is this the applicant of the ticket? For downloading a finished request's
 * product from public tracking without an account: the e-mail or the
 * WhatsApp number given on the request, as the applicant's account holds it
 * or as the ticket itself recorded it (older requests keep a contact of
 * their own). Ticket numbers are easy to guess, so only what the applicant
 * knows counts; the counter's made-up e-mail (…@walkin.local, a number and a
 * domain) does not, the number does.
 */
class TicketOwnerVerifier
{
    public function matches(Ticket $ticket, ?string $contact): bool
    {
        $contact = trim((string) $contact);
        if ($contact === '' || ! $ticket->user) {
            return false;
        }

        return str_contains($contact, '@')
            ? $this->emails($ticket)->contains(fn (string $email) => hash_equals($email, mb_strtolower($contact)))
            : $this->phones($ticket)->contains(fn (string $phone) => Applicants::samePhone($phone, $contact));
    }

    /** The applicant's e-mails, but not the counter's made-up ones. */
    private function emails(Ticket $ticket): \Illuminate\Support\Collection
    {
        return collect([$ticket->user->email, $ticket->email])
            ->map(fn (?string $email) => mb_strtolower(trim((string) $email)))
            ->filter(fn (string $email) => $email !== '' && ! str_ends_with($email, '@walkin.local'))
            ->unique();
    }

    /** The applicant's WhatsApp numbers. */
    private function phones(Ticket $ticket): \Illuminate\Support\Collection
    {
        return collect([$ticket->user->whatsapp_number, $ticket->whatsapp_number])->filter()->unique();
    }
}
