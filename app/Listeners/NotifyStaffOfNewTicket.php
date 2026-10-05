<?php

namespace App\Listeners;

use App\Events\TicketSubmitted;
use App\Models\User;
use App\Services\DispositionAuthority;
use App\Support\TicketLabels;
use App\Support\TicketNotification;
use Illuminate\Support\Collection;

/**
 * Notifikasi "permohonan baru masuk": the back office, and when the service
 * needs a disposition, the leaders who may dispose it. The person who
 * registered the request is not notified about their own action.
 */
class NotifyStaffOfNewTicket
{
    public function handle(TicketSubmitted $event): void
    {
        $ticket = $event->ticket->loadMissing('service:id,name', 'user:id,name');

        foreach (self::recipients($event) as $user) {
            $leader = $ticket->approval_required && app(DispositionAuthority::class)->canDispose($user, $ticket);

            TicketNotification::send(
                $user,
                $ticket,
                $leader ? 'Permohonan baru menunggu disposisi' : 'Permohonan baru masuk',
                "{$ticket->ticket_number} · {$ticket->service?->name} · {$ticket->user?->name} (" . TicketLabels::mode($ticket->mode) . ')',
                $leader ? 'heroicon-o-clipboard-document-check' : 'heroicon-o-inbox-arrow-down',
                $leader ? 'warning' : 'info',
            );
        }
    }

    /** @return Collection<int, User> */
    public static function recipients(TicketSubmitted $event): Collection
    {
        $ticket = $event->ticket;
        $staff = User::role('back_office')->where('is_active', true)->get();
        if ($ticket->approval_required) {
            $staff = $staff->merge(app(DispositionAuthority::class)->disposers($ticket));
        }

        return $staff->unique('id')->reject(fn (User $user) => $user->id === $event->actor?->id || $user->id === $ticket->user_id)->values();
    }
}
