<?php

namespace App\Policies;

use App\Models\SuratKeluar;
use App\Models\User;
use App\Support\ActiveRoles;

/**
 * Buku surat keluar: every pemroses naskah (back office — Tata Usaha, the
 * Waka units, penjamin mutu) reads the register, takes numbers and fills in
 * letters. Only an administrator deletes a letter, with a reason kept in the
 * audit log: a gap in the register must stay explainable.
 */
class SuratKeluarPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->processor($user);
    }

    public function view(User $user, SuratKeluar $suratKeluar): bool
    {
        return $this->processor($user);
    }

    /** Take one or more numbers. */
    public function create(User $user): bool
    {
        return $this->processor($user);
    }

    /** Fill in or correct a reserved letter, including its attachments. */
    public function update(User $user, SuratKeluar $suratKeluar): bool
    {
        return $this->processor($user);
    }

    /** Administrators only (in the admin role context), one letter at a time. */
    public function delete(User $user, SuratKeluar $suratKeluar): bool
    {
        return $user->is_active !== false && ActiveRoles::hasRole($user, 'admin');
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    private function processor(User $user): bool
    {
        return $user->is_active !== false && $user->can('backoffice.access');
    }
}
