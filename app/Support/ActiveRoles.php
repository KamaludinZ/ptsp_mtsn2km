<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

/**
 * Peran aktif petugas di /cp: peran staf yang dipegang user (Spatie) dan
 * peran mana yang sedang dipakai sebagai konteks kerja (users.active_role_id).
 * Peran yang dimiliki tidak berubah; yang dipilih hanya konteksnya.
 */
class ActiveRoles
{
    /** Peran aktif sesi ini (tiap sesi masuk punya konteksnya sendiri). */
    public const SESSION_KEY = 'active_role';

    /** Peran aktif yang ditetapkan middleware untuk request ini. */
    public const REQUEST_ATTRIBUTE = 'active_role';

    /** Penanda lama "wajib memilih peran" di sesi; tidak dipakai lagi, hanya dibersihkan. */
    public const PICK_FLAG = 'active_role_pick';

    /**
     * Saat masuk: peran aktif langsung ditetapkan tanpa harus memilih dulu,
     * yaitu pilihan terakhir user bila masih dipegang, selain itu peran
     * staf pertama yang ia pegang. Ia tetap bisa berganti peran di header.
     */
    public static function startSession(User $user, \Illuminate\Contracts\Session\Session $session): void
    {
        $session->forget([self::SESSION_KEY, self::PICK_FLAG]);

        if ($user->isStaff() && ($role = self::current($user)) !== null) {
            $session->put(self::SESSION_KEY, $role);

            // Jejak audit: dengan peran apa petugas multi-peran mulai bekerja.
            if ($user->roles->whereIn('name', User::STAFF_ROLES)->count() > 1) {
                self::record($user, null, $role, 'signed_in');
            }
        }
    }

    /** Penjelasan singkat konteks kerja tiap peran yang bisa dipilih. */
    public const DESCRIPTIONS = [
        'admin' => 'Kelola pengguna, peran, layanan, integrasi notifikasi, dan keamanan sistem.',
        'kepala_sekolah' => 'Setujui permohonan layanan dan beri disposisi ke unit pemroses.',
        'kepala_tu' => 'Setujui permohonan, awasi loket dan back office tata usaha.',
        'supervisor' => 'Pantau hasil survei dan kinerja layanan.',
        'back_office' => 'Proses tiket layanan yang masuk dan unggah dokumen hasil.',
        'front_desk' => 'Layani pemohon walk-in, buku tamu, dan serah-terima hasil di loket.',
        'waka_humas' => 'Proses naskah hasil disposisi untuk unit kehumasan.',
        'waka_kesiswaan' => 'Proses naskah hasil disposisi untuk unit kesiswaan.',
        'waka_kurikulum' => 'Proses naskah hasil disposisi untuk unit kurikulum.',
        'waka_sarpras' => 'Proses naskah hasil disposisi untuk unit sarana prasarana.',
        'tata_usaha' => 'Proses naskah hasil disposisi untuk unit tata usaha.',
        'penjamin_mutu' => 'Proses naskah hasil disposisi untuk unit penjaminan mutu.',
    ];

    public const ICONS = [
        'admin' => 'heroicon-o-shield-check',
        'kepala_sekolah' => 'heroicon-o-academic-cap',
        'kepala_tu' => 'heroicon-o-briefcase',
        'supervisor' => 'heroicon-o-eye',
        'back_office' => 'heroicon-o-inbox-stack',
        'front_desk' => 'heroicon-o-building-storefront',
    ];

    /**
     * Peran staf yang dipegang user, urut seperti User::STAFF_ROLES.
     *
     * @return Collection<int, array{name: string, label: string, description: string, icon: string, areas: list<string>, active: bool, last_used: ?\Illuminate\Support\Carbon}>
     */
    public static function for(User $user): Collection
    {
        $held = $user->getRoleNames();
        $active = self::inContext($user);
        // Waktu pilihan terakhir (bisa dari sesi lain) menempel pada peran itu.
        $lastChosen = $user->activeRole?->name;

        return collect(User::STAFF_ROLES)
            ->filter(fn (string $role) => $held->contains($role))
            ->map(fn (string $role) => self::row($role, $role === $active, $role === $lastChosen ? $user->active_role_at : null))
            ->values();
    }

    /**
     * Nama peran aktif user: pilihan terakhirnya bila masih dipegang,
     * selain itu peran pimpinan yang dipegang, lalu peran staf pertama
     * (urutan User::STAFF_ROLES).
     * Null hanya bila ia tidak memegang peran staf sama sekali.
     */
    public static function current(User $user): ?string
    {
        $held = collect(User::STAFF_ROLES)->filter(fn (string $role) => $user->hasRole($role))->values();
        $chosen = $user->activeRole?->name;

        if ($chosen !== null && $held->contains($chosen)) {
            return $chosen;
        }

        // Belum pernah memilih: peran pimpinan lebih dulu, lalu urutan User::STAFF_ROLES.
        return $held->sortBy(fn (string $role) => array_search($role, User::LEADERSHIP_ROLES, true) === false ? 1 : 0)->first();
    }

    /**
     * Peran pemohon (mis. Guru, Pegawai) yang juga dipegang petugas. Peran
     * ini tidak menjadi konteks kerja di /cp; memilihnya membuka portal
     * pemohon untuk mengajukan dan memantau permohonan sendiri.
     *
     * @return Collection<int, array{name: string, label: string, description: string, icon: string, areas: list<string>, active: bool, last_used: null, portal: true}>
     */
    public static function portalRolesFor(User $user): Collection
    {
        return $user->getRoleNames()
            ->filter(fn (string $role) => Applicants::isApplicantRole($role))
            ->map(fn (string $role) => [
                'name' => $role,
                'label' => RoleAccess::roleLabel($role),
                'description' => 'Ajukan dan pantau permohonan layanan Anda sendiri sebagai ' . RoleAccess::roleLabel($role) . ' lewat portal pemohon.',
                'icon' => 'heroicon-o-user',
                'areas' => ['Portal Pemohon'],
                'active' => false,
                'last_used' => null,
                'portal' => true,
            ])
            ->values();
    }

    /**
     * Jadikan $role peran aktif user. Hanya peran staf yang benar-benar
     * dipegang user (Spatie) yang boleh dipilih; peran lain ditolak sebagai
     * kesalahan validasi pada field "peran".
     *
     * @return array{from: ?string, to: string} peran asal (null bila belum ada) dan tujuan
     *
     * @throws ValidationException
     */
    public static function switchTo(User $user, string $role): array
    {
        if (! in_array($role, User::STAFF_ROLES, true) || ! $user->hasRole($role)) {
            throw ValidationException::withMessages(['peran' => 'Peran ini tidak Anda pegang.']);
        }

        $request = request();
        $ownSession = $request->hasSession() && $request->user()?->is($user);
        $from = $ownSession ? self::resolve($user, $request->session()->get(self::SESSION_KEY)) : self::current($user);
        $choosingAfterSignIn = $ownSession && (bool) $request->session()->get(self::PICK_FLAG);

        $user->forceFill([
            'active_role_id' => Role::findByName($role, 'web')->getKey(),
            'active_role_at' => now(),
        ])->save();
        $user->unsetRelation('activeRole');

        // Sesi ini langsung memakai peran baru; request berikutnya membacanya lewat middleware.
        if ($ownSession) {
            $request->session()->forget(self::PICK_FLAG);
            $request->session()->put(self::SESSION_KEY, $role);
            $request->attributes->set(self::REQUEST_ATTRIBUTE, $role);
        }

        if ($from !== $role || $choosingAfterSignIn) {
            self::record($user, $from, $role, $choosingAfterSignIn ? 'chosen' : 'switched');
        }

        return ['from' => $from, 'to' => $role];
    }

    /**
     * Rekam jejak perpindahan peran (activity_log "role", tidak dapat diubah):
     * siapa, dari peran apa ke peran apa, kapan, dan dari mana.
     */
    private static function record(User $user, ?string $from, string $to, string $event): void
    {
        $request = request();

        activity('role')
            ->causedBy($user)
            ->performedOn($user)
            ->event($event)
            ->withProperties(array_filter([
                'dari' => $from,
                'ke' => $to,
                'dari_label' => $from ? RoleAccess::roleLabel($from) : null,
                'ke_label' => RoleAccess::roleLabel($to),
                'sumber' => $request->is('api/*') ? 'api' : 'web',
                'halaman' => $request->is('api/*') ? null : (parse_url((string) $request->headers->get('referer'), PHP_URL_PATH) ?: $request->path()),
                'ip' => $request->ip(),
                'perangkat' => mb_substr((string) $request->userAgent(), 0, 200) ?: null,
            ], fn ($v) => $v !== null))
            ->log(match ($event) {
                'chosen' => 'Memilih peran ' . RoleAccess::roleLabel($to) . ' saat masuk',
                'signed_in' => 'Masuk dengan peran ' . RoleAccess::roleLabel($to),
                default => 'Berpindah peran ' . ($from ? RoleAccess::roleLabel($from) . ' → ' : '') . RoleAccess::roleLabel($to),
            });
    }

    /**
     * Peran aktif untuk sebuah sesi: yang tersimpan di sesi bila masih
     * dipegang, selain itu pilihan terakhir user (current()).
     */
    public static function resolve(User $user, ?string $sessionRole): ?string
    {
        if ($sessionRole !== null && in_array($sessionRole, User::STAFF_ROLES, true) && $user->hasRole($sessionRole)) {
            return $sessionRole;
        }

        return self::current($user);
    }

    /**
     * Peran aktif yang berlaku pada request ini: ditetapkan middleware
     * SetActiveRoleContext; tanpa middleware (mis. API token) dihitung
     * dari pilihan terakhir user.
     */
    public static function inContext(?User $user = null): ?string
    {
        $request = request();
        $user ??= $request->user();
        if (! $user instanceof User) {
            return null;
        }

        if ($request->user()?->is($user) && $request->attributes->has(self::REQUEST_ATTRIBUTE)) {
            return $request->attributes->get(self::REQUEST_ATTRIBUTE);
        }

        return self::current($user);
    }

    /**
     * Wewenang mengikuti peran aktif: hanya peran aktif (bukan semua peran
     * yang dipegang) yang dihitung.
     */
    public static function hasRole(User $user, string|array $roles): bool
    {
        $active = self::inContext($user);

        return $active !== null && in_array($active, (array) $roles, true);
    }

    /**
     * Peran aktif yang dicatat pada jejak aksi petugas (riwayat layanan,
     * disposisi): peran aktif bila ia petugas, selain itu null (pemohon,
     * sistem).
     */
    public static function actingRoleOf(?User $user): ?string
    {
        return $user && $user->isStaff() ? self::inContext($user) : null;
    }

    /** Peran aktif adalah peran pimpinan (memberi disposisi). */
    public static function actsAsLeader(?User $user, bool $includeAdmin = true): bool
    {
        $leaders = $includeAdmin ? RoleAccess::LEADERSHIP : array_diff(RoleAccess::LEADERSHIP, ['admin']);

        return $user !== null && self::hasRole($user, array_values($leaders));
    }

    /** Izin (Spatie) yang diberikan peran aktif, atau langsung ke akun itu. */
    public static function can(User $user, string $permission): bool
    {
        $active = self::inContext($user);
        $role = $active ? Role::findByName($active, 'web') : null;

        return ($role?->hasPermissionTo($permission, 'web') ?? false)
            || rescue(fn () => $user->hasDirectPermission($permission), false, report: false);
    }

    /** Peran aktif kini selalu ditetapkan otomatis, jadi tidak ada lagi yang wajib memilih. */
    public static function needsChoice(User $user): bool
    {
        return self::current($user) === null && self::for($user)->isNotEmpty();
    }

    /** Area kerja yang dibuka sebuah peran, dalam bahasa menu panel. */
    public static function areas(string $role): array
    {
        $labels = ['frontdesk.access' => 'Loket', 'backoffice.access' => 'Back Office', 'supervision.access' => 'Pengawasan'];

        return array_values(array_filter([
            in_array($role, RoleAccess::LEADERSHIP, true) ? 'Pimpinan' : null,
            ...array_map(fn ($permission) => $labels[$permission] ?? null, RoleAccess::ROLE_PERMISSIONS[$role] ?? []),
            $role === 'admin' ? 'Manajemen Sistem' : null,
        ]));
    }

    public static function row(string $name, bool $active = false, mixed $lastUsed = null): array
    {
        return [
            'name' => $name,
            'label' => RoleAccess::roleLabel($name),
            'description' => self::DESCRIPTIONS[$name] ?? 'Peran petugas di panel staf.',
            'icon' => self::ICONS[$name] ?? 'heroicon-o-user',
            'areas' => self::areas($name),
            'active' => $active,
            'last_used' => $lastUsed,
        ];
    }
}
