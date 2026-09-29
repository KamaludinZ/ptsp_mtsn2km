<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visitor;

/**
 * Guest book (Modul 1): the counter registers and checks guests out;
 * corrections and deletions are left to administrators.
 */
class VisitorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('frontdesk.access');
    }

    public function view(User $user, Visitor $visitor): bool
    {
        return $user->can('frontdesk.access');
    }

    public function create(User $user): bool
    {
        return $user->can('frontdesk.access');
    }

    public function checkOut(User $user, Visitor $visitor): bool
    {
        return $user->can('frontdesk.access');
    }

    public function update(User $user, Visitor $visitor): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Visitor $visitor): bool
    {
        return $user->hasRole('admin');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
