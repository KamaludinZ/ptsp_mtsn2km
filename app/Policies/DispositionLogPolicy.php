<?php

namespace App\Policies;

use App\Models\DispositionLog;
use App\Models\User;

/**
 * Riwayat disposisi: staff who may see the ticket may read its
 * dispositions; only the deciding leader (or an administrator) adds the
 * signed sheet afterwards; nobody changes or removes a disposition.
 */
class DispositionLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, DispositionLog $disposition): bool
    {
        return $user->isStaff() && $user->can('view', $disposition->ticket);
    }

    /** Upload the TTD scan / TTE file later, for a disposition signed that way. */
    public function uploadSignature(User $user, DispositionLog $disposition): bool
    {
        return (bool) $disposition->signature_model?->needsFile()
            && ($disposition->actor_id === $user->id || $user->hasRole('admin'));
    }

    public function update(User $user, DispositionLog $disposition): bool
    {
        return false;
    }

    public function delete(User $user, DispositionLog $disposition): bool
    {
        return false;
    }
}
