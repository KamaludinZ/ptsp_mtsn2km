<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use App\Services\DispositionAuthority;
use App\Support\ActiveRoles;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Every staff role reads the ticket list; applicants see their own in the portal.
        return $user->isStaff();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // The applicant, and staff who handle tickets.
        return $user->id === $ticket->user_id
            || $user->hasAnyRole(User::STAFF_ROLES)
            || $user->can('backoffice.access')
            || $user->can('frontdesk.access');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Walk-in requests are registered from the counter page, online ones by applicants.
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    /** Working a ticket: back office officers (and admins), in that active role. */
    public function update(User $user, Ticket $ticket): bool
    {
        return ActiveRoles::can($user, 'backoffice.access');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->hasRole('admin');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /** Handing a finished product over at the counter (Modul 9). */
    public function handOver(User $user, Ticket $ticket): bool
    {
        return ActiveRoles::can($user, 'frontdesk.access');
    }

    /**
     * Disposing / rejecting a ticket: only while the active role is one of
     * the service's disposing roles, not merely because the user holds it.
     */
    public function approve(User $user, Ticket $ticket): Response
    {
        $authority = app(DispositionAuthority::class);

        if ($authority->canDisposeInActiveRole($user, $ticket)) {
            return Response::allow();
        }

        // A leader of this ticket working in another role is told which role to activate.
        return $authority->canDispose($user, $ticket)
            ? Response::deny(\App\Exceptions\OutsideActiveRoleException::for($user, 'memutuskan permohonan ini', $authority->roles($ticket->service))->getMessage())
            : Response::deny('Anda tidak berwenang memutuskan permohonan ini.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return false;
    }
}
