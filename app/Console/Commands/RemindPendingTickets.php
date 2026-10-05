<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Ticket;
use App\Models\User;
use App\Services\DispositionAuthority;
use App\Support\FollowUpReminders;
use App\Support\TicketNotification;
use Illuminate\Console\Command;

/**
 * Pengingat permohonan tertunda (every working morning): requests past their
 * target date, requests waiting more than a day for the leader's
 * disposition, and follow-ups due. Each person gets at most one reminder
 * per request and kind per day.
 */
class RemindPendingTickets extends Command
{
    public const DISPOSITION_WAIT_HOURS = 24;

    protected $signature = 'tickets:remind-pending';

    protected $description = 'Kirim pengingat in-app untuk permohonan terlambat, menunggu disposisi, dan tindak lanjut yang jatuh tempo';

    private int $sent = 0;

    public function handle(DispositionAuthority $authority): int
    {
        $this->sent = 0; // the command instance can be reused within one process
        $backOffice = User::role('back_office')->where('is_active', true)->get();

        foreach (Ticket::overdue()->with('assignedTo', 'service:id,name')->get() as $ticket) {
            $days = (int) $ticket->estimated_completion_date->diffInDays(today());
            foreach ($ticket->assignedTo?->is_active ? collect([$ticket->assignedTo]) : $backOffice as $user) {
                $this->remind($user, $ticket, 'overdue', 'Permohonan melewati target', "{$ticket->ticket_number} · {$ticket->service?->name} terlambat {$days} hari.", 'heroicon-o-exclamation-triangle', 'danger');
            }
        }

        $waiting = Ticket::awaitingApproval()->where('updated_at', '<=', now()->subHours(self::DISPOSITION_WAIT_HOURS))->with('service:id,name')->get();
        foreach ($waiting as $ticket) {
            foreach ($authority->disposers($ticket) as $leader) {
                $this->remind($leader, $ticket, 'disposition', 'Menunggu disposisi Anda', "{$ticket->ticket_number} · {$ticket->service?->name} belum didisposisi.", 'heroicon-o-clipboard-document-check', 'warning');
            }
        }

        foreach (User::role(User::STAFF_ROLES)->where('is_active', true)->get() as $user) {
            foreach (FollowUpReminders::for($user)->whereIn('state', ['overdue', 'today']) as $item) {
                $this->remind($user, $item['ticket'], 'follow_up', 'Tindak lanjut jatuh tempo', $item['ticket']->ticket_number . ' · ' . FollowUpReminders::label($item) . ': ' . mb_strimwidth($item['note'], 0, 80, '…'), 'heroicon-o-clock', 'info');
            }
        }

        $this->info("{$this->sent} pengingat dikirim.");

        return self::SUCCESS;
    }

    private function remind(User $user, Ticket $ticket, string $kind, string $title, string $body, string $icon, string $color): void
    {
        $already = Notification::where('notifiable_type', $user->getMorphClass())->where('notifiable_id', $user->id)
            ->where('ticket_id', $ticket->id)
            ->whereRaw("data->'viewData'->>'reminder' = ?", [$kind])
            ->where('created_at', '>=', today())
            ->exists();

        if (! $already) {
            TicketNotification::send($user, $ticket, $title, $body, $icon, $color, ['reminder' => $kind]);
            $this->sent++;
        }
    }
}
