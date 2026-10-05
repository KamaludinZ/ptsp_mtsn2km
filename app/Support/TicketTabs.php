<?php

namespace App\Support;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Tabs of the request list (Kelola Permohonan), shared by the panel and the
 * API: which a user gets, how each limits the list, and the default one.
 */
class TicketTabs
{
    public const LABELS = [
        'antrian' => 'Antrian',
        'saya' => 'Tugas Saya',
        'disposisi-unit' => 'Disposisi unit saya',
        'terlambat' => 'Terlambat',
        'persetujuan' => 'Menunggu Persetujuan',
        'siap-diambil' => 'Siap Diambil',
        'semua' => 'Semua',
    ];

    /** @return array<int, string> tabs available to $user, in display order */
    public static function for(User $user): array
    {
        return array_values(array_filter([
            $user->can('backoffice.access') ? 'antrian' : null,
            $user->can('backoffice.access') ? 'saya' : null,
            ProcessorRoles::unitsOf($user) ? 'disposisi-unit' : null,
            'terlambat',
            'persetujuan',
            'siap-diambil',
            'semua',
        ]));
    }

    public static function default(User $user): string
    {
        return match (true) {
            $user->can('backoffice.access') => 'antrian',
            $user->hasRole('front_desk') => 'siap-diambil',
            default => 'semua',
        };
    }

    /** Limit $query to $tab; with $ordered the tab's own order applies (the queue: nearest target first). */
    public static function apply(Builder $query, string $tab, User $user, bool $ordered = false): Builder
    {
        return match ($tab) {
            'antrian' => $query->whereIn('status', ['submitted', 'verified', 'in_process'])
                ->when($ordered, fn (Builder $q) => $q->reorder()->orderByRaw('estimated_completion_date asc nulls last')->orderBy('created_at')),
            'saya' => $query->open()->where('assigned_to_id', $user->id),
            'disposisi-unit' => $query->forwardedTo(ProcessorRoles::unitsOf($user))->open(),
            'terlambat' => $query->overdue(),
            'persetujuan' => $query->awaitingApproval(),
            'siap-diambil' => $query->where('status', 'completed')->where('ready_for_pickup', true),
            default => $query,
        };
    }

    /** @return array<string, int> number of tickets per available tab */
    public static function counts(User $user, ?callable $narrow = null): array
    {
        return collect(self::for($user))
            ->mapWithKeys(function (string $tab) use ($user, $narrow) {
                $query = Ticket::query();
                if ($narrow) {
                    $narrow($query);
                }

                return [$tab => self::apply($query, $tab, $user)->count()];
            })
            ->all();
    }
}
