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

    /** Sesi baru petugas multi-peran: wajib memilih peran sebelum membuka /cp. */
    public const PICK_FLAG = 'active_role_pick';

    /**
     * Saat masuk: petugas yang memegang lebih dari satu peran staf memilih
     * peran aktif dulu (pilihan terakhirnya tetap ditandai). True bila ia
     * harus diarahkan ke halaman Pilih Peran Aktif.
     */
    public static function startSession(User $user, \Illuminate\Contracts\Session\Session $session): bool
    {
        $session->forget(self::SESSION_KEY);

        if (! $user->isStaff() || self::for($user)->count() < 2) {
            $session->forget(self::PICK_FLAG);

            return false;
        }

        $session->put(self::PICK_FLAG, true);

        return true;
    }

    /** Penjelasan singkat konteks kerja tiap peran yang bisa dipilih. */
    public const DESCRIPTIONS = [
        'admin' => 'Kelola pengguna, peran, layanan, integrasi notifikasi, dan keamanan sistem.',
        'kepala_sekolah' => 'Setujui permohonan layanan dan beri disposisi ke unit pemroses.',
        'kepala_tu' => 'Setujui permohonan, awasi loket dan back office tata usaha.',
        'supervisor' => 'Tindak lanjuti pengaduan, WBS, dan pantau hasil survei.',
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
     * Nama peran aktif user, atau null bila belum memilih / peran itu
     * sudah tidak dipegang lagi. Satu-satunya peran staf otomatis aktif.
     */
    public static function current(User $user): ?string
    {
        $held = collect(User::STAFF_ROLES)->filter(fn (string $role) => $user->hasRole($role))->values();
        $chosen = $user->activeRole?->name;

        return match (true) {
            $chosen !== null && $held->contains($chosen) => $chosen,
            $held->count() === 1 => $held->first(),
            default => null,
        };
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

        return ['from' => $from, 'to' => $role];
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
     * yang dipegang) yang dihitung. Petugas multi-peran yang belum memilih
     * belum berwenang apa pun; petugas satu peran tidak berubah.
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

    /** Petugas multi-peran yang belum punya peran aktif harus memilih dulu. */
    public static function needsChoice(User $user): bool
    {
        return self::current($user) === null && self::for($user)->count() > 1;
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
