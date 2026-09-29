<?php

namespace App\Services;

use App\Exceptions\TicketActionException;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * The counter (Loket): guests who come to meet someone (Modul 1) and walk-in
 * applicants whose request the officer registers on their behalf.
 */
class FrontDeskService
{
    public const APPLICANT_TYPES = [
        'guru' => 'Guru',
        'pegawai' => 'Pegawai',
        'siswa' => 'Siswa',
        'walimurid' => 'Wali Murid',
        'alumni' => 'Alumni',
        'instansi' => 'Instansi',
        'umum' => 'Umum',
    ];

    public function __construct(private TicketService $tickets)
    {
    }

    /**
     * @param  array{name: string, email?: ?string, phone?: ?string, institution?: ?string, purpose: string, person_to_meet_id: int, photo_path?: ?string}  $data
     */
    public function registerGuest(array $data, User $actor): Visitor
    {
        $host = User::find($data['person_to_meet_id']);

        $visitor = Visitor::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'institution' => filled($data['institution'] ?? null) ? $data['institution'] : '-',
            'purpose' => $data['purpose'],
            'person_to_meet' => $host?->name,
            'photo_path' => $data['photo_path'] ?? null,
            'check_in_time' => now(),
            'created_by' => $actor->id,
            'status' => 'active',
        ]);

        Log::info('Visitor registered at the counter', ['visitor_id' => $visitor->id]);

        return $visitor;
    }

    public function checkOut(Visitor $visitor): void
    {
        if ($visitor->check_out_time) {
            throw new TicketActionException('Tamu sudah check out.');
        }

        $visitor->update(['check_out_time' => now(), 'status' => 'checked_out']);
    }

    /**
     * Register a walk-in applicant's request (mode offline).
     *
     * @param  array{service_id: int, applicant_name: string, applicant_email?: ?string, applicant_phone: string, applicant_type: string, description: string, priority?: ?string}  $data
     * @param  array<string, string>  $files  stored path => original file name
     */
    public function registerWalkIn(array $data, array $files, User $actor): Ticket
    {
        $service = Service::where('is_active', true)->findOrFail($data['service_id']);

        return $this->tickets->open(
            $service,
            $this->findOrCreateApplicant($data),
            $actor,
            'offline',
            $data['description'],
            $data['priority'] ?? 'normal',
            $files,
        );
    }

    /** An existing account with the same e-mail or WhatsApp number, or a new one. */
    public function findOrCreateApplicant(array $data): User
    {
        $email = filled($data['applicant_email'] ?? null) ? Str::lower(trim($data['applicant_email'])) : null;
        $phone = trim((string) ($data['applicant_phone'] ?? ''));

        $user = User::query()
            ->where(function ($query) use ($email, $phone) {
                if ($email) {
                    $query->orWhere('email', $email);
                }
                if ($phone !== '') {
                    $query->orWhere('whatsapp_number', $phone);
                }
            })
            ->first();

        if ($user) {
            return $user;
        }

        // e-mail is required and unique on users; walk-in applicants cannot
        // always give one, so it is derived from their phone number.
        return User::create([
            'name' => $data['applicant_name'],
            'email' => $email ?? (preg_replace('/\D+/', '', $phone) ?: Str::random(10)) . '@walkin.local',
            'whatsapp_number' => $phone !== '' ? $phone : null,
            'user_type' => array_key_exists($data['applicant_type'] ?? '', self::APPLICANT_TYPES) ? $data['applicant_type'] : 'umum',
            'password' => bcrypt(Str::random(32)),
            'is_active' => true,
        ]);
    }
}
