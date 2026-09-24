<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
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
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        return $user->id === $ticket->created_by || $user->hasRole(['admin', 'supervisor']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->id === $ticket->created_by || $user->hasRole(['admin', 'supervisor']);
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, Ticket $ticket): bool
    {
        $service = $ticket->service;
        
        $canApprove = false;
        
        // Check if user has required role
        if ($service->approval_roles) {
            foreach ($service->approval_roles as $role) {
                if ($user->hasRole($role)) {
                    $canApprove = true;
                    break;
                }
            }
        }
        
        // Check if user is specifically allowed
        if (!$canApprove && $service->approval_users) {
            if (in_array($user->id, $service->approval_users)) {
                $canApprove = true;
            }
        }
        
        // If no specific settings, allow any admin/supervisor
        if (!$canApprove && !$service->approval_roles && !$service->approval_users) {
            $canApprove = $user->hasRole(['admin', 'supervisor']);
        }
        
        return $canApprove;
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
