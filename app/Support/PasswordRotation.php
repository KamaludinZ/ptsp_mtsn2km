<?php

namespace App\Support;

use App\Models\User;

/**
 * Pengingat ganti kata sandi di dalam panel (bukan e-mail/WhatsApp). Dua pemicu:
 * akun hasil import yang baru dipakai pertama kali, dan kata sandi yang sudah
 * berumur 6 bulan. Pengguna boleh mengabaikannya untuk sesi ini.
 */
class PasswordRotation
{
    public const REASON_IMPORTED = 'imported';

    public const REASON_PERIODIC = 'periodic';

    public const MONTHS = 6;

    public const DISMISS_KEY = 'password_rotation_dismissed';

    /** Alasan pengingat untuk pengguna ini, atau null bila tidak perlu diingatkan. */
    public static function reason(?User $user): ?string
    {
        if (! $user || session(self::DISMISS_KEY)) {
            return null;
        }

        // Kolom pelacak waktu ganti kata sandi menyusul; sementara pengingat hanya
        // tampil sebagai contoh di lingkungan lokal.
        return app()->isLocal() ? self::REASON_PERIODIC : null;
    }

    public static function message(string $reason): string
    {
        return $reason === self::REASON_IMPORTED
            ? 'Akun Anda dibuat oleh administrator dengan kata sandi sementara. Ganti kata sandi agar hanya Anda yang mengetahuinya.'
            : 'Kata sandi Anda sudah dipakai lebih dari ' . self::MONTHS . ' bulan. Ganti secara berkala agar akun tetap aman.';
    }

    public static function dismiss(): void
    {
        session([self::DISMISS_KEY => true]);
    }
}
