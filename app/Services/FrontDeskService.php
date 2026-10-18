<?php

namespace App\Services;

use App\Exceptions\TicketActionException;
use App\Filament\Portal\Resources\TicketResource as PortalTicketResource;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Visitor;
use App\Models\VisitorMaster;
use App\Support\ApplicantLink;
use App\Support\Applicants;
use App\Support\RoleAccess;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

/**
 * The counter (Loket): guests who come to meet someone (Modul 1) and walk-in
 * applicants whose request the officer registers on their behalf.
 */
class FrontDeskService
{
    /** Labels of the applicant types; the codes are the official applicant roles (App\Support\Applicants::ROLES). */
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

    /** Validation rules for a guest registering themselves (public guest book or kiosk). */
    public static function selfRegistrationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|string|email|max:255',
            'institution' => 'nullable|string|max:255',
            'institution_category' => 'nullable|string|max:255',
            'purpose' => ['required', 'string', Rule::in(VisitorMaster::options('tujuan'))],
            'purpose_other' => 'required_if:purpose,' . VisitorMaster::OTHER . '|nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'obscure_name' => 'nullable', // checkbox "on" from the web form, true/false from the API
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    /**
     * A guest registering themselves: no officer, checked in now.
     *
     * @param  array<string, mixed>  $data  selfRegistrationRules() fields; an uploaded `photo` is stored with the staff-taken photos
     */
    public function selfRegister(array $data): Visitor
    {
        $photo = ($data['photo'] ?? null) instanceof \Illuminate\Http\UploadedFile
            ? $data['photo']->store('visitor-photos', 'local')
            : null;

        return Visitor::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'institution' => $data['institution'] ?? null,
            'institution_category' => $data['institution_category'] ?? null,
            'purpose' => $data['purpose'] === VisitorMaster::OTHER ? $data['purpose_other'] : $data['purpose'],
            'notes' => $data['notes'] ?? null,
            'is_obscured' => filter_var($data['obscure_name'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'photo_path' => $photo,
            'check_in_time' => now(),
            'status' => 'active',
        ]);
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
     * @param  array{service_id: int, applicant_name?: string, applicant_email?: ?string, applicant_phone?: string, applicant_type: string, description: string, priority?: ?string}  $data
     * @param  array<string, string>  $files  stored path => original file name
     * @param  ?User  $applicant  an already registered applicant picked by the officer; otherwise found or created from $data
     */
    public function registerWalkIn(array $data, array $files, User $actor, ?User $applicant = null): Ticket
    {
        return $this->registerWalkInLinked($data, $files, $actor, $applicant)[0];
    }

    /**
     * registerWalkIn() plus the outcome of linking the applicant's account
     * (picked, linked to an existing one, or new). The account and the ticket
     * are saved together: a refused ticket leaves no new account behind.
     *
     * @return array{0: Ticket, 1: ApplicantLink}
     */
    public function registerWalkInLinked(array $data, array $files, User $actor, ?User $applicant = null): array
    {
        // The service may have been switched off while the form was open.
        $service = Service::where('is_active', true)->find($data['service_id'])
            ?? throw new TicketActionException('Layanan ini sedang tidak menerima permohonan. Pilih layanan lain.');

        // Layanan tersaring sesuai tipe pemohon (Service::scopeAvailableFor), juga di loket.
        $type = Applicants::isApplicantRole($data['applicant_type'] ?? null) ? $data['applicant_type'] : 'umum';
        if (! Service::whereKey($service->getKey())->availableFor($type)->exists()) {
            throw new TicketActionException('Layanan ' . $service->name . ' tidak tersedia untuk pemohon ' . RoleAccess::roleLabel($type) . '. Pilih layanan lain atau periksa tipe pemohon.');
        }

        // Pemohon terdaftar dipakai apa adanya (tanpa akun baru), asal masih aktif dan tergolong pemohon.
        if ($applicant && ($applicant->is_active === false || ! $applicant->isApplicant())) {
            throw new TicketActionException('Akun ' . $applicant->name . ' tidak aktif atau bukan akun pemohon; pilih akun pemohon lain.');
        }

        return DB::transaction(function () use ($data, $files, $actor, $applicant, $service) {
            $link = $applicant ? new ApplicantLink($applicant, ApplicantLink::PICKED) : $this->resolveApplicant($data);

            $ticket = $this->tickets->open(
                $service,
                $link->user,
                $actor,
                'offline',
                $data['description'],
                $data['priority'] ?? 'normal',
                $files,
            );

            return [$ticket, $link];
        });
    }

    /**
     * The account a walk-in applicant is linked to: the one with the same
     * e-mail (unique, deleted accounts included) or, failing that, the oldest
     * active applicant account with the same WhatsApp number (punctuation and
     * the 62/0 prefix ignored, Applicants::wherePhone) — a number may be
     * shared, e.g. by a parent and child, and a staff member's number never
     * catches a walk-in's request. None when neither is given or nothing matches.
     */
    public function findApplicant(array $data): ?User
    {
        $email = filled($data['applicant_email'] ?? null) ? Str::lower(trim($data['applicant_email'])) : null;
        $phone = trim((string) ($data['applicant_phone'] ?? ''));

        if ($email !== null && ($user = User::withTrashed()->where('email', $email)->first())) {
            return $user;
        }

        return Applicants::normalizePhone($phone) !== ''
            ? Applicants::wherePhone(User::applicants()->where('is_active', true), $phone)->orderBy('id')->first()
            : null;
    }

    /** Why a matched account cannot take a walk-in's request (null: it can). */
    public static function linkProblem(User $user): ?string
    {
        return match (true) {
            $user->trashed() => 'Email ini milik akun yang sudah dihapus. Pakai email lain atau kosongkan email.',
            $user->is_active === false => 'Akun ' . $user->name . ' dengan email/WhatsApp ini dinonaktifkan. Minta administrator mengaktifkannya, atau pakai email lain.',
            ! $user->isApplicant() => 'Email ini milik akun petugas, bukan akun pemohon. Pakai email lain atau kosongkan email.',
            default => null,
        };
    }

    /**
     * Pendaftaran portal: akun pemohon dari loket yang cocok dengan email atau
     * WhatsApp pendaftar dan belum diklaim siapa pun, untuk disambungkan alih-
     * alih membuat akun ganda. Seperti loket (findApplicant), email didahulukan;
     * lewat WhatsApp hanya akun loket tanpa email asli dan hanya bila tepat
     * satu yang cocok, karena satu nomor bisa dipakai bersama. Null: buat
     * akun baru.
     *
     * @throws ValidationException email milik akun yang sudah dipakai (atau dihapus)
     */
    public function findClaimableApplicant(string $email, string $phone): ?User
    {
        $email = Str::lower(trim($email));
        $byEmail = User::withTrashed()->where('email', $email)->first();

        if ($byEmail) {
            if (self::emailTaken($email)) {
                throw ValidationException::withMessages(['email' => self::EMAIL_TAKEN]);
            }

            return $byEmail;
        }

        if (Applicants::normalizePhone($phone) === '') {
            return null;
        }

        // Hanya bila tepat satu akun loket cocok; lebih dari satu tidak bisa dipastikan milik siapa.
        $matches = Applicants::wherePhone(User::where('email', 'like', '%@walkin.local'), $phone)->get()->filter(fn (User $user) => self::isUnclaimed($user));

        return $matches->count() === 1 ? $matches->first() : null;
    }

    /**
     * Pendaftaran portal, satu jalur dengan loket: akun loket yang cocok
     * (findClaimableApplicant) disambungkan beserta riwayat permohonannya,
     * selain itu akun baru dibuat. Keduanya memegang peran pemohon $type.
     *
     * @param  array{name: string, email: string, whatsapp_number: string, password: string}  $account  password sudah di-hash
     *
     * @throws ValidationException email milik akun yang sudah dipakai
     */
    public function registerPortalApplicant(array $account, string $type, ?string $registrationCode = null): ApplicantLink
    {
        $linked = $this->findClaimableApplicant($account['email'], $account['whatsapp_number']);

        if (! $linked) {
            $user = User::create($account + ['user_type' => $type, 'registration_code' => $registrationCode, 'is_active' => true]);
            $user->assignRole(Role::findOrCreate($type, 'web'));

            return new ApplicantLink($user, ApplicantLink::CREATED);
        }

        // Akun loket tanpa email asli hanya bisa cocok lewat WhatsApp.
        $matchedBy = $linked->email === Str::lower(trim($account['email'])) ? 'email' : 'whatsapp';

        $linked->update($account + array_filter([
            // Tipe lama hanya diganti bila akun belum punya peran pemohon.
            'user_type' => $linked->hasAnyRole(Applicants::ROLES) ? null : $type,
            'registration_code' => $registrationCode,
        ]));
        $linked->assignRole(Role::findOrCreate($type, 'web'));

        activity('audit')
            ->performedOn($linked)
            ->withProperties(['dicocokkan_lewat' => $matchedBy])
            ->log('Pendaftaran portal disambungkan ke akun pemohon dari loket');

        // Rincian riwayat dibaca di portal, yang baru terbuka setelah email terverifikasi.
        $tickets = $linked->tickets()->count();
        Notification::make()
            ->title('Akun tersambung dengan data dari loket')
            ->body($tickets > 0
                ? $tickets . ' permohonan yang didaftarkan di loket PTSP kini tampil di Permohonan Saya.'
                : 'Permohonan yang nanti didaftarkan di loket PTSP juga masuk ke akun ini.')
            ->icon('heroicon-o-link')
            ->iconColor('success')
            ->actions([NotificationAction::make('open')->label('Lihat permohonan')->url(PortalTicketResource::getUrl('index', panel: 'portal'))->markAsRead()])
            ->sendToDatabase($linked);

        return new ApplicantLink($linked, ApplicantLink::LINKED, $matchedBy);
    }

    public const EMAIL_TAKEN = 'Email ini sudah terdaftar. Silakan masuk, atau gunakan "Lupa kata sandi" bila lupa.';

    /**
     * Email yang tidak bisa dipakai mendaftar di portal: milik akun yang sudah
     * dipakai pemiliknya atau sudah dihapus. Email akun loket yang belum
     * diklaim boleh dipakai: pendaftaran menyambungkannya.
     */
    public static function emailTaken(string $email): bool
    {
        $user = User::withTrashed()->where('email', Str::lower(trim($email)))->first();

        return $user !== null && ($user->trashed() || ! self::isUnclaimed($user));
    }

    /**
     * Akun yang dibuat atas nama pemohon (loket, admin) dan belum pernah dipakai
     * pemiliknya: aktif, pemohon menurut aturan pemohon resmi (sama dengan
     * loket dan tab Pemohon) dan bukan petugas, belum pernah masuk, email
     * belum terverifikasi. Akun yang dinonaktifkan admin tidak bisa diaktifkan
     * lagi lewat pendaftaran.
     */
    public static function isUnclaimed(User $user): bool
    {
        return $user->is_active !== false && $user->isApplicant() && ! $user->isStaff()
            && $user->last_login_at === null && $user->email_verified_at === null;
    }

    /**
     * The linked account (findApplicant), or a new applicant account.
     *
     * @throws TicketActionException the matched account cannot take the request (linkProblem)
     */
    public function findOrCreateApplicant(array $data): User
    {
        return $this->resolveApplicant($data)->user;
    }

    /**
     * Penautan akun walk-in beserta hasilnya: akun yang sudah ada (tertaut,
     * lewat email atau WhatsApp) atau akun baru.
     *
     * @throws TicketActionException the matched account cannot take the request (linkProblem)
     */
    public function resolveApplicant(array $data): ApplicantLink
    {
        $email = filled($data['applicant_email'] ?? null) ? Str::lower(trim($data['applicant_email'])) : null;
        $phone = trim((string) ($data['applicant_phone'] ?? ''));
        // e-mail is required and unique on users; walk-in applicants cannot
        // always give one, so it is derived from their (normalised) phone number.
        $placeholder = $email === null ? (Applicants::normalizePhone($phone) ?: Str::random(10)) . '@walkin.local' : null;

        return DB::transaction(function () use ($data, $email, $phone, $placeholder) {
            // Cegah akun ganda: dua petugas yang mendaftarkan orang yang sama
            // bersamaan menunggu giliran, lalu yang kedua menemukan akun yang pertama.
            foreach (collect([$email, Applicants::normalizePhone($phone)])->filter()->sort()->values() as $key) {
                DB::select('select pg_advisory_xact_lock(hashtext(?))', ['pemohon:' . $key]);
            }

            $user = $this->findApplicant($data)
                // Akun loket lama dengan email bayangan yang sama (mis. nomornya sempat diubah).
                ?? ($placeholder ? User::withTrashed()->where('email', $placeholder)->first() : null);

            if ($user) {
                if ($problem = self::linkProblem($user)) {
                    throw new TicketActionException($problem);
                }

                // Akun lama tanpa peran apa pun: ditautkan sebagai pemohon resmi lewat perannya.
                if ($user->roles->isEmpty()) {
                    $user->assignRole(Role::findOrCreate($user->user_type, 'web'));
                }

                // Email bayangan berasal dari nomor WhatsApp.
                return new ApplicantLink($user, ApplicantLink::LINKED, $email !== null && $user->email === $email ? 'email' : 'whatsapp');
            }

            $type = Applicants::isApplicantRole($data['applicant_type'] ?? null) ? $data['applicant_type'] : 'umum';

            $user = User::create([
                'name' => $data['applicant_name'],
                'email' => $email ?? $placeholder,
                'whatsapp_number' => $phone !== '' ? $phone : null,
                'user_type' => $type,
                'password' => bcrypt(Str::random(32)),
                'is_active' => true,
            ]);

            // Pemohon ditandai lewat perannya (aturan pemohon resmi), seperti pendaftar portal.
            $user->assignRole(Role::findOrCreate($type, 'web'));

            return new ApplicantLink($user, ApplicantLink::CREATED);
        });
    }
}
