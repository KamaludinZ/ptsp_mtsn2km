<?php

namespace App\Support;

use App\Filament\Portal\Resources\TicketResource as PortalTicketResource;
use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Notifications\DatabaseNotification;

/**
 * In-app notification about a request: one click opens the request where
 * the recipient works on it (staff panel) or follows it (applicant portal).
 */
class TicketNotification
{
    /** @param  array<string, mixed>  $extra  more viewData, e.g. ['reminder' => 'overdue'] */
    public static function send(User $recipient, Ticket $ticket, string $title, ?string $body = null, string $icon = 'heroicon-o-bell', string $color = 'info', array $extra = []): void
    {
        Notification::make()
            ->title($title)
            ->body($body)
            ->icon($icon)
            ->iconColor($color)
            ->viewData(['ticket_number' => $ticket->ticket_number] + $extra)
            ->actions([Action::make('open')->label('Buka permohonan')->url(self::url($ticket, $recipient))->markAsRead()])
            ->sendToDatabase($recipient);
    }

    /** Where $user opens $ticket: the staff panel, their own portal page, or nowhere. */
    public static function url(Ticket $ticket, User $user): ?string
    {
        return match (true) {
            $user->isStaff() && $user->can('view', $ticket) => TicketResource::getUrl('view', ['record' => $ticket], panel: 'admin'),
            $ticket->user_id === $user->id => PortalTicketResource::getUrl('view', ['record' => $ticket], panel: 'portal'),
            default => null,
        };
    }

    /** The link of a stored notification for its reader (falls back to the ticket number). */
    public static function urlOf(DatabaseNotification $notification, User $reader): ?string
    {
        if ($url = $notification->data['actions'][0]['url'] ?? $notification->data['url'] ?? null) {
            return $url;
        }

        $number = $notification->data['viewData']['ticket_number'] ?? $notification->data['ticket_number'] ?? null;
        $ticket = $number ? Ticket::where('ticket_number', $number)->first() : null;

        return $ticket ? self::url($ticket, $reader) : null;
    }
}
