<?php

namespace App\Support;

use App\Filament\Pages\FrontDesk\RegisterService;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Who is looking at /services and a service page, worked out once per
 * request: which services they see, and how they may request one. Staff
 * serve every applicant at the counter, so they see every service (also an
 * inactive one's page); applicants and guests see what is open to their type.
 */
final class ServiceVisitor
{
    private function __construct(
        public readonly ?User $user,
        public readonly bool $staff,
        /** Requests through the portal (guests after logging in). */
        public readonly bool $portal,
        /** May register a walk-in request at the counter instead. */
        public readonly bool $counter,
    ) {}

    public static function current(): self
    {
        $user = auth()->user();

        return new self($user, (bool) $user?->isStaff(), Applicants::usesPortal($user), $user !== null && RegisterService::canAccess());
    }

    /** The active services listed for this visitor in the catalog. */
    public function catalog(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->when(! $this->staff, fn (Builder $query) => $query->availableFor($this->userType()));
    }

    /** Services whose page this visitor may open: staff also an inactive one. */
    public function pages(Builder $query): Builder
    {
        return $query->when(! $this->staff, fn (Builder $query) => $query->where('is_active', true)->availableFor($this->userType()));
    }

    /**
     * Is the apply button theirs? Guests log in through it; a signed-in
     * visitor needs the portal and a service open to their type online.
     */
    public function canApply(Service $service): bool
    {
        return $service->is_active
            && in_array($service->mode, ['online', 'hybrid'], true)
            && $this->portal
            && ($this->user === null || $service->isAvailableFor($this->userType()));
    }

    private function userType(): string
    {
        return $this->user?->user_type ?: 'umum';
    }
}
