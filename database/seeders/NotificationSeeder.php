<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use App\Support\TicketLabels;
use App\Support\TicketNotification;
use Illuminate\Database\Seeder;

/** Contoh notifikasi in-app (demo data, never seeded in production). */
class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $tickets = Ticket::with('service:id,name', 'user')->latest()->limit(6)->get();
        if ($tickets->isEmpty()) {
            return;
        }

        $staff = User::whereIn('email', ['staff1@mtsn2malang.sch.id', 'kepsek@mtsn2malang.sch.id', 'ptsp@mtsn2malang.sch.id'])->get();

        foreach ($staff as $user) {
            foreach ($tickets->take(4) as $i => $ticket) {
                [$title, $body, $icon, $color] = match ($i % 3) {
                    0 => ['Permohonan baru masuk', "{$ticket->ticket_number} · {$ticket->service?->name}", 'heroicon-o-inbox-arrow-down', 'info'],
                    1 => ['Disposisi untuk unit Anda', "{$ticket->ticket_number} perlu ditindaklanjuti.", 'heroicon-o-arrow-right-circle', 'warning'],
                    default => ['Status permohonan berubah', "{$ticket->ticket_number} kini " . TicketLabels::status($ticket->status) . '.', 'heroicon-o-arrow-path', 'success'],
                };
                TicketNotification::send($user, $ticket, $title, $body, $icon, $color);
                if ($i > 1) {
                    $user->notifications()->latest()->first()?->markAsRead();
                }
            }
        }

        foreach ($tickets as $ticket) {
            if ($ticket->user && ! $ticket->user->isStaff()) {
                TicketNotification::send($ticket->user, $ticket, 'Permohonan Anda diperbarui', "{$ticket->ticket_number} kini " . TicketLabels::status($ticket->status) . '.');
            }
        }
    }
}
