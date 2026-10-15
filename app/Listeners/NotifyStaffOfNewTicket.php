<?php

namespace App\Listeners;

use App\Events\TicketSubmitted;
use App\Models\User;
use App\Models\Ticket;
use App\Services\DispositionAuthority;
use App\Services\NotificationDispatcher;
use App\Support\RoleAccess;
use App\Support\TicketLabels;
use App\Support\TicketNotification;
use Illuminate\Support\Collection;

/**
 * Notifikasi "permohonan baru masuk": the back office, and when the service
 * needs a disposition, the leaders who may dispose it (Kepala Madrasah
 * and/or Kepala TU, following the service's disposition mode). Leaders also
 * get it by email and WhatsApp (template "disposition_requested"), with the
 * steps to dispose. The person who registered the request is not notified
 * about their own action.
 */
class NotifyStaffOfNewTicket
{
    public function handle(TicketSubmitted $event): void
    {
        // The whole service: its disposition settings decide who is a leader here.
        $ticket = $event->ticket->loadMissing('service', 'user:id,name');

        foreach (self::recipients($event) as $user) {
            $leader = $ticket->approval_required && app(DispositionAuthority::class)->canDispose($user, $ticket);

            $summary = "{$ticket->ticket_number} · {$ticket->service?->name} · {$ticket->user?->name} (" . TicketLabels::mode($ticket->mode) . ')';
            $role = $leader ? self::disposingRoleLabel($user, $ticket) : null;

            TicketNotification::send(
                $user,
                $ticket,
                $leader ? 'Permohonan baru menunggu disposisi' : 'Permohonan baru masuk',
                $leader ? "{$summary}. Buka permohonan dengan peran aktif {$role}, klik \"Disposisikan\", pilih unit tujuan dan instruksi, lalu kirim." : $summary,
                $leader ? 'heroicon-o-clipboard-document-check' : 'heroicon-o-inbox-arrow-down',
                $leader ? 'warning' : 'info',
            );

            if ($leader) {
                app(NotificationDispatcher::class)->send('disposition_requested', $ticket, $user, [
                    'pemohon' => $ticket->user?->name ?? '',
                    'peran' => $role,
                ]);
            }
        }
    }

    /**
     * The role(s) $user disposes $ticket in, for the steps in the message:
     * "Kepala Madrasah", "Kepala Madrasah atau Kepala Tata Usaha".
     */
    public static function disposingRoleLabel(User $user, Ticket $ticket): string
    {
        $roles = collect(app(DispositionAuthority::class)->roles($ticket->service))
            ->filter(fn (string $role) => $user->hasRole($role));

        // Named on the service in person (approval_users): any of their leadership roles.
        if ($roles->isEmpty()) {
            $roles = collect(RoleAccess::LEADERSHIP)->filter(fn (string $role) => $user->hasRole($role));
        }

        return $roles->isEmpty()
            ? 'peran pimpinan Anda'
            : $roles->map(fn (string $role) => RoleAccess::roleLabel($role))->join(', ', ' atau ');
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
