<?php

namespace App\Policies;

use App\Models\PersuratanMaster;
use App\Models\User;
use App\Support\ActiveRoles;

/**
 * Master Persuratan lists, Jenis SK included: the same access as the surat
 * keluar register. Every pemroses naskah (back office) reads, adds and edits
 * the choices; only an administrator (in the admin role context) deletes
 * them. Deactivated accounts get nothing.
 */
class PersuratanMasterPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->processor($user);
    }

    public function view(User $user, PersuratanMaster $master): bool
    {
        return $this->processor($user);
    }

    public function create(User $user): bool
    {
        return $this->processor($user);
    }

    public function update(User $user, PersuratanMaster $master): bool
    {
        return $this->processor($user);
    }

    /** Letters and SK that used the choice keep the text they were saved with. */
    public function delete(User $user, PersuratanMaster $master): bool
    {
        return $this->admin($user);
    }

    /** Bulk delete in the Master Persuratan table. */
    public function deleteAny(User $user): bool
    {
        return $this->admin($user);
    }

    private function processor(User $user): bool
    {
        return $user->is_active !== false && $user->can('backoffice.access');
    }

    private function admin(User $user): bool
    {
        return $user->is_active !== false && ActiveRoles::hasRole($user, 'admin');
    }
}
