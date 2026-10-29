<?php

namespace App\Support;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * The services an applicant sees in the portal: active, and open to any of
 * the applicant's roles (an account may hold several, e.g. a teacher who is
 * also a parent). Shared by the portal's catalogue and its request form.
 */
final class PortalServices
{
    /**
     * The applicant's roles; an account without any, its applicant type.
     *
     * @return list<string>
     */
    public static function rolesOf(User $user): array
    {
        return Applicants::rolesOf($user) ?: [$user->user_type ?: 'umum'];
    }

    public static function offered(User $user): Builder
    {
        $roles = self::rolesOf($user);

        return Service::query()
            ->where('is_active', true)
            ->where(fn (Builder $query) => collect($roles)->each(fn (string $role) => $query->orWhere(fn (Builder $open) => $open->availableFor($role))));
    }

    /** offered() that can be requested online: the portal's request form, its API and TicketService::open. */
    public static function requestable(User $user): Builder
    {
        return Service::query()->requestable(self::rolesOf($user), 'online');
    }
}
