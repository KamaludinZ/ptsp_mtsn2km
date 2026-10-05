<?php

namespace App\Support;

use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Pengingat tindak lanjut: open requests whose latest follow-up note
 * planned a next step that is overdue, due today or within the next days,
 * for the officer who wrote it or the one the request is assigned to.
 */
class FollowUpReminders
{
    public const AHEAD_DAYS = 3;

    /**
     * @return Collection<int, array{ticket: Ticket, due: Carbon, note: string, type: ?string, state: 'overdue'|'today'|'soon', days: int}>
     */
    public static function for(User $user): Collection
    {
        // Each ticket's latest follow-up note: a newer note replaces the plan,
        // and one without a date means nothing more is planned.
        $latest = TicketLog::query()
            ->whereIn('action', ['note_added', 'applicant_note'])
            ->whereHas('ticket', fn ($q) => $q->open()->where(fn ($q) => $q->where('assigned_to_id', $user->id)
                ->orWhereHas('logs', fn ($l) => $l->whereIn('action', ['note_added', 'applicant_note'])->where('performed_by', $user->id))))
            ->orderByDesc('created_at')->orderByDesc('id')
            ->with('ticket.service:id,name', 'ticket.user:id,name')
            ->get()
            ->unique('ticket_id')
            ->filter(fn (TicketLog $log) => filled($log->metadata['next_follow_up_at'] ?? null));

        $horizon = today()->addDays(self::AHEAD_DAYS);

        return $latest
            ->map(function (TicketLog $log) {
                $due = Carbon::parse($log->metadata['next_follow_up_at'])->startOfDay();
                $days = (int) today()->diffInDays($due, false);

                return [
                    'ticket' => $log->ticket,
                    'due' => $due,
                    'note' => (string) $log->notes,
                    'type' => $log->metadata['follow_up_type'] ?? null,
                    'state' => $days < 0 ? 'overdue' : ($days === 0 ? 'today' : 'soon'),
                    'days' => $days,
                ];
            })
            ->filter(fn (array $item) => $item['due']->lte($horizon))
            ->sortBy(fn (array $item) => $item['due']->timestamp)
            ->values();
    }

    public static function label(array $item): string
    {
        return match ($item['state']) {
            'overdue' => 'Terlambat ' . abs($item['days']) . ' hari',
            'today' => 'Hari ini',
            default => $item['days'] === 1 ? 'Besok' : "{$item['days']} hari lagi",
        };
    }
}
