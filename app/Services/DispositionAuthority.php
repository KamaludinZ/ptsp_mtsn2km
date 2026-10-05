<?php

namespace App\Services;

use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Support\RoleAccess;
use App\Support\ServiceDisposition;
use Illuminate\Support\Collection;

/**
 * Kewenangan disposisi: who may dispose a ticket, from its service's
 * disposition mode (or a hand-made list of roles/users), and who those
 * leaders are.
 */
class DispositionAuthority
{
    /**
     * Roles allowed to dispose requests for this service. Without a usable
     * setting (not configured, or since switched to "tanpa disposisi" while
     * older tickets still wait) the school leadership decides.
     */
    public function roles(?Service $service): array
    {
        if (! $service || ! $service->approval_required || ServiceDisposition::usesDefault($service)) {
            return RoleAccess::LEADERSHIP;
        }

        return array_values((array) $service->approval_roles);
    }

    /** Whether a ticket needs a disposition is fixed when it is opened (tickets.approval_required). */
    public function canDispose(User $user, Ticket $ticket): bool
    {
        $service = $ticket->service;

        if (! $ticket->approval_required || ! $service) {
            return false;
        }

        if (in_array((string) $user->id, array_map('strval', (array) $service->approval_users), true)) {
            return true;
        }

        return $user->hasAnyRole($this->roles($service));
    }

    /** @return Collection<int, User> the leaders who may dispose this ticket */
    public function disposers(Ticket $ticket): Collection
    {
        $service = $ticket->service;
        if (! $ticket->approval_required || ! $service) {
            return collect();
        }

        $roles = $this->roles($service);

        return User::query()
            ->where('is_active', true)
            ->where(fn ($q) => $q
                ->when($roles, fn ($q) => $q->role($roles))
                ->orWhereIn('id', array_map('intval', (array) $service->approval_users)))
            ->orderBy('name')
            ->get();
    }
}
