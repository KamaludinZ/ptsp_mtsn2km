<?php

namespace App\Policies;

use App\Models\Pengumuman;
use App\Models\User;

/**
 * Pengumuman (Manajemen Konten): only active administrators write or see
 * announcements that aren't on the site; everyone may read the live ones.
 * Roles live on the "web" guard, so they are checked there explicitly (API
 * requests come in on "sanctum").
 */
class PengumumanPolicy
{
    public static function isContentAdmin(?User $user): bool
    {
        return $user !== null && $user->is_active !== false && $user->hasRole('admin', 'web');
    }

    public function viewAny(User $user): bool
    {
        return self::isContentAdmin($user);
    }

    /** Live announcements are public; drafts, scheduled and ended ones only for administrators. */
    public function view(?User $user, Pengumuman $pengumuman): bool
    {
        return $pengumuman->status() === 'tayang' || self::isContentAdmin($user);
    }

    public function create(User $user): bool
    {
        return self::isContentAdmin($user);
    }

    public function update(User $user, Pengumuman $pengumuman): bool
    {
        return self::isContentAdmin($user);
    }

    /** Tayangkan, jadikan draf, akhiri. */
    public function publish(User $user, Pengumuman $pengumuman): bool
    {
        return self::isContentAdmin($user);
    }

    public function delete(User $user, Pengumuman $pengumuman): bool
    {
        return self::isContentAdmin($user);
    }

    public function deleteAny(User $user): bool
    {
        return self::isContentAdmin($user);
    }
}
