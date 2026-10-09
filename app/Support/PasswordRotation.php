<?php

namespace App\Support;

use App\Models\User;

/**
 * Pengingat ganti kata sandi di dalam panel (bukan e-mail/WhatsApp). Dua pemicu:
 * akun hasil import yang baru dipakai pertama kali (must_change_password), dan
 * kata sandi yang sudah berumur 6 bulan (password_changed_at).
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

        if ($user->must_change_password) {
            return self::REASON_IMPORTED;
        }

        $changedAt = $user->password_changed_at ?? $user->created_at;

        return $changedAt && $changedAt->lte(now()->subMonths(self::MONTHS)) ? self::REASON_PERIODIC : null;
    }

    public static function message(string $reason): string
    {
        return $reason === self::REASON_IMPORTED
            ? 'Akun Anda dibuat oleh administrator dengan kata sandi sementara. Ganti kata sandi agar hanya Anda yang mengetahuinya.'
            : 'Kata sandi Anda sudah dipakai lebih dari ' . self::MONTHS . ' bulan. Ganti secara berkala agar akun tetap aman.';
    }

    /**
     * "Ingatkan nanti": pengingat berkala disembunyikan sampai sesi berikutnya;
     * pengingat akun hasil import hanya tampil sekali, jadi penandanya dihapus.
     */
    public static function dismiss(?User $user): void
    {
        session([self::DISMISS_KEY => true]);

        if ($user?->must_change_password) {
            $user->forceFill(['must_change_password' => false])->saveQuietly();
        }
    }
}
