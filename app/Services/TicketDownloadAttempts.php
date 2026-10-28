<?php

namespace App\Services;

use App\Models\Ticket;
use App\Support\TicketTracking;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Batas percobaan & kunci for downloading a finished request's product from
 * public tracking without an account. A ticket gets ATTEMPTS tries at the
 * applicant's e-mail or WhatsApp number; the last one wrong locks it for
 * LOCK_MINUTES, for everyone, since the next guess may come from another
 * address. One address also gets only NETWORK_ATTEMPTS wrong tries an hour
 * over all tickets: numbers are sequential, so guessing would walk them.
 *
 * A try is counted before the contact is compared, so requests sent at once
 * cannot slip past the limit, and while locked nothing is compared at all.
 * The right contact clears the ticket's count.
 */
class TicketDownloadAttempts
{
    public const ATTEMPTS = 3;

    public const LOCK_MINUTES = 15;

    public const NETWORK_ATTEMPTS = 10;

    public function __construct(private TicketOwnerVerifier $verifier) {}

    /**
     * Seconds until the ticket, or this address, may be tried again; 0 when it may now.
     */
    public function lockedFor(Ticket $ticket, ?string $ip): int
    {
        return max(
            RateLimiter::tooManyAttempts($this->lockKey($ticket), 1) ? RateLimiter::availableIn($this->lockKey($ticket)) : 0,
            RateLimiter::tooManyAttempts($this->networkKey($ip), self::NETWORK_ATTEMPTS) ? RateLimiter::availableIn($this->networkKey($ip)) : 0,
        );
    }

    /**
     * Tries the contact. matched: it is the applicant's; left: tries remaining
     * on the ticket after a wrong one; locked: seconds to wait, once locked
     * (by this try, or before it).
     *
     * @return array{matched: bool, left: int, locked: int}
     */
    public function attempt(Ticket $ticket, ?string $contact, ?string $ip): array
    {
        if ($locked = $this->lockedFor($ticket, $ip)) {
            return ['matched' => false, 'left' => 0, 'locked' => $locked];
        }

        $tries = RateLimiter::increment($this->countKey($ticket), self::LOCK_MINUTES * 60);
        if ($tries <= self::ATTEMPTS && $this->verifier->matches($ticket, $contact)) {
            RateLimiter::clear($this->countKey($ticket));

            return ['matched' => true, 'left' => self::ATTEMPTS, 'locked' => 0];
        }

        RateLimiter::increment($this->networkKey($ip), 3600);
        if ($tries >= self::ATTEMPTS) {
            RateLimiter::clear($this->countKey($ticket));
            if (! RateLimiter::tooManyAttempts($this->lockKey($ticket), 1)) {
                RateLimiter::increment($this->lockKey($ticket), self::LOCK_MINUTES * 60);
            }
        }

        return ['matched' => false, 'left' => max(0, self::ATTEMPTS - $tries), 'locked' => $this->lockedFor($ticket, $ip)];
    }

    /**
     * The reply to "verify, then download", the same from /tracking/unduh and
     * POST /api/publik/lacak/{nomor}/unduh (one count for both): 200 {unduh},
     * the signed link to the file; 422 {message, sisa_percobaan}, a wrong
     * contact; 429 {message, coba_lagi_detik}, locked; 404, no file to give.
     */
    public function answer(?Ticket $ticket, ?string $contact, ?string $ip): JsonResponse
    {
        if (! $ticket || ! TicketTracking::hasOutputFile($ticket)) {
            return response()->json(['message' => 'Hasil layanan tiket ini belum dapat diunduh.'], 404);
        }

        $try = $this->attempt($ticket, $contact, $ip);
        if ($try['matched']) {
            return response()->json(['unduh' => TicketTracking::outputLink($ticket)]);
        }
        if ($try['locked']) {
            return response()->json([
                'message' => 'Verifikasi dikunci sementara karena terlalu banyak percobaan yang salah. Coba lagi dalam ' . (int) ceil($try['locked'] / 60) . ' menit, masuk ke akun pemohon, atau ambil hasilnya di Ruang PTSP.',
                'coba_lagi_detik' => $try['locked'],
            ], 429, ['Retry-After' => $try['locked']]);
        }

        return response()->json([
            'message' => 'Email atau nomor WhatsApp tidak cocok dengan data pemohon tiket ini.',
            'sisa_percobaan' => $try['left'],
        ], 422);
    }

    private function countKey(Ticket $ticket): string
    {
        return 'unduh-hasil:' . $ticket->getKey();
    }

    private function lockKey(Ticket $ticket): string
    {
        return 'unduh-hasil-kunci:' . $ticket->getKey();
    }

    private function networkKey(?string $ip): string
    {
        return 'unduh-hasil-jaringan:' . ($ip ?: 'tanpa-alamat');
    }
}
