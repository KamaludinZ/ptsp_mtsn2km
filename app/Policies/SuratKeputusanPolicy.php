<?php

namespace App\Policies;

use App\Models\SuratKeputusan;
use App\Models\Ticket;
use App\Models\User;
use App\Support\RegisterSkAccess;

/**
 * Register Penomoran SK, under the rules of the surat keluar register but
 * by the active role (RegisterSkAccess): every pemroses naskah (back office)
 * reads the register, asks for numbers and fills in the SK; only an
 * administrator deletes one.
 */
class SuratKeputusanPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->processor($user);
    }

    public function view(User $user, SuratKeputusan $suratKeputusan): bool
    {
        return $this->processor($user);
    }

    /** Ask for an SK number. */
    public function create(User $user): bool
    {
        return $this->processor($user);
    }

    /**
     * Ask for an SK number for a PTSP ticket (Permohonan terkait): who may
     * ask for numbers and may work that ticket (TicketPolicy::update), as
     * "Proses tiket > Minta Nomor SK" is offered. A deleted ticket takes none.
     */
    public function createForTicket(User $user, Ticket $ticket): bool
    {
        return $this->create($user) && ! $ticket->trashed() && $user->can('update', $ticket);
    }

    public function update(User $user, SuratKeputusan $suratKeputusan): bool
    {
        return $this->processor($user);
    }

    /** Administrators only (in the admin role context), one SK at a time. */
    public function delete(User $user, SuratKeputusan $suratKeputusan): bool
    {
        return RegisterSkAccess::canDelete($user);
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    private function processor(User $user): bool
    {
        return RegisterSkAccess::canWork($user);
    }
}
