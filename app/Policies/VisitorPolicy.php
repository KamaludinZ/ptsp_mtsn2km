<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visitor;

/**
 * Guest book (Modul 1): the counter registers guests, notes their visit and
 * checks them out; administrators may correct an entry; nobody deletes one.
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

    /** A note about the visit (who they met, what is pending); not a correction of the guest's data. */
    public function note(User $user, Visitor $visitor): bool
    {
        return $user->can('frontdesk.access');
    }

    public function update(User $user, Visitor $visitor): bool
    {
        return $user->hasRole('admin');
    }

    /** The guest book is a record of who entered: entries are corrected, never removed. */
    public function delete(User $user, Visitor $visitor): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
