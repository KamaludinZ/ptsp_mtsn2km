<?php

namespace App\Support;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;

/**
 * Aturan pemohon resmi: siapa yang tergolong pemohon, dipakai bersama oleh
 * loket (pilih pemohon terdaftar), tab Pemohon di daftar pengguna, dan
 * penautan akun. Pemohon adalah akun yang memegang salah satu peran pemohon
 * (daftar putih); peran staf dan pimpinan bukan pemohon. Akun walk-in lama
 * yang belum punya peran sama sekali ikut tergolong lewat tipe usernya.
 */
class Applicants
{
    /** Peran pemohon resmi, urut seperti pilihan di loket. */
    public const ROLES = ['siswa', 'walimurid', 'alumni', 'umum', 'instansi', 'guru', 'pegawai'];

    /** Peran staf dan pimpinan: bukan pemohon. */
    public const NON_APPLICANT_ROLES = User::STAFF_ROLES;

    public static function isApplicantRole(?string $role): bool
    {
        return in_array($role, self::ROLES, true);
    }

    /** Peran pemohon dengan labelnya ("siswa" => "Siswa"), urut ROLES. */
    public static function options(): array
    {
        return collect(self::ROLES)->mapWithKeys(fn (string $role) => [$role => RoleAccess::roleLabel($role)])->all();
    }

    /** Apakah akun ini pemohon (memegang peran pemohon, atau akun lama tanpa peran bertipe pemohon)? */
    public static function isApplicant(?User $user): bool
    {
        return $user !== null && self::rolesOf($user) !== [];
    }

    /**
     * Apakah pengunjung ini mengajukan layanan lewat portal? Pengunjung yang
     * belum masuk: ya (setelah login); staf hanya bila juga memegang peran
     * pemohon, selain itu portal mengembalikannya ke /cp.
     */
    public static function usesPortal(?User $user): bool
    {
        return $user === null || $user->canAccessPanel(Filament::getPanel('portal'));
    }

    /**
     * Peran pemohon yang dipegang akun, urut ROLES. Akun lama tanpa peran
     * apa pun: tipe usernya bila termasuk peran pemohon.
     *
     * @return list<string>
     */
    public static function rolesOf(User $user): array
    {
        $held = $user->getRoleNames();

        if ($held->isEmpty()) {
            return self::isApplicantRole($user->user_type) ? [$user->user_type] : [];
        }

        return array_values(array_filter(self::ROLES, fn (string $role) => $held->contains($role)));
    }

    /**
     * Akun pemohon aktif yang bisa dipilih petugas loket: bertipe $type
     * (semua tipe bila kosong) dan cocok dengan $term pada nama, email, atau
     * nomor WhatsApp (tanda baca nomor diabaikan). Urut nama.
     */
    public static function lookup(?string $term = null, ?string $type = null): Builder
    {
        $term = trim((string) $term);
        $like = '%' . $term . '%';

        return User::query()
            ->where('is_active', true)
            ->applicants($type)
            ->when($term !== '', fn (Builder $query) => $query->where(fn (Builder $q) => $q
                ->where('name', 'ilike', $like)
                ->orWhere('email', 'ilike', $like)
                ->orWhere(fn (Builder $q) => self::wherePhoneContains($q, $term))))
            ->orderBy('name');
    }

    /**
     * Batasi $query ke akun yang nomor WhatsApp-nya memuat $term: apa adanya,
     * atau angkanya saja dengan awalan 62 disamakan dengan 0 ("62812-34" ketemu "0812 3456").
     */
    public static function wherePhoneContains(Builder $query, string $term): Builder
    {
        $digits = preg_replace('/^62/', '0', preg_replace('/\D+/', '', $term));

        return $query->where(fn (Builder $q) => $q
            ->where('whatsapp_number', 'ilike', '%' . trim($term) . '%')
            ->when($digits !== '', fn (Builder $q) => $q->orWhereRaw("regexp_replace(regexp_replace(coalesce(whatsapp_number, ''), '\\D', '', 'g'), '^62', '0') like ?", ['%' . $digits . '%'])));
    }

    /**
     * Nomor WhatsApp baku untuk pencocokan akun: angka saja dengan awalan 0
     * ("+62 812-3456-7890", "6281234567890", "81234567890" -> "081234567890").
     */
    public static function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        return $digits === '' ? '' : preg_replace('/^(62|0)?/', '0', $digits);
    }

    /**
     * Apakah $typed nomor yang sama dengan $registered, ditulis cara apa pun:
     * tanda baca diabaikan, awalan 62, +62, atau 0062 sama dengan 0. Nomor
     * terdaftar yang kosong tidak cocok dengan apa pun.
     */
    public static function samePhone(?string $registered, ?string $typed): bool
    {
        $normalize = fn (?string $phone) => self::normalizePhone(preg_replace('/^\s*(\+|00)/', '', (string) $phone));
        $expected = $normalize($registered);

        return $expected !== '' && hash_equals($expected, $normalize($typed));
    }

    /** Batasi $query ke akun bernomor WhatsApp sama dengan $phone (tanda baca dan awalan 62/0 diabaikan). */
    public static function wherePhone(Builder $query, string $phone): Builder
    {
        return $query->whereRaw(
            "regexp_replace(regexp_replace(coalesce(whatsapp_number, ''), '\\D', '', 'g'), '^(62|0)?', '0') = ?",
            [self::normalizePhone($phone)],
        );
    }

    /** Batasi $query ke akun pemohon; dengan $type, hanya pemohon bertipe itu. */
    public static function scope(Builder $query, ?string $type = null): Builder
    {
        // Tipe di luar daftar putih (mis. peran staf) tidak menghasilkan pemohon.
        $roles = filled($type) ? array_values(array_intersect([$type], self::ROLES)) : self::ROLES;

        return $query->where(fn (Builder $q) => $q
            ->whereHas('roles', fn (Builder $r) => $r->whereIn('name', $roles))
            ->orWhere(fn (Builder $q) => $q->whereIn('user_type', $roles)->whereDoesntHave('roles')));
    }
}
