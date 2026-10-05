<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\DatabaseNotification;

/**
 * Notifikasi in-app (Laravel's database channel) with a link to the request
 * it is about. ticket_id is filled from the ticket number the notification
 * carries (see App\Support\TicketNotification).
 */
class Notification extends DatabaseNotification
{
    protected static function booted(): void
    {
        static::creating(function (Notification $notification) {
            if (! $notification->ticket_id && ($number = $notification->ticketNumber())) {
                $notification->ticket_id = Ticket::where('ticket_number', $number)->value('id');
            }
        });
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function ticketNumber(): ?string
    {
        return $this->data['viewData']['ticket_number'] ?? $this->data['ticket_number'] ?? null;
    }

    public function title(): string
    {
        return (string) ($this->data['title'] ?? '');
    }

    public function body(): ?string
    {
        return filled($this->data['body'] ?? null) ? strip_tags($this->data['body']) : null;
    }

    public function scopeForTicket($query, Ticket $ticket)
    {
        return $query->where('ticket_id', $ticket->id);
    }
}
