<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One attempt to send a notification (riwayat notifikasi). */
class NotificationDelivery extends Model
{
    public const CHANNELS = ['email' => 'Email', 'whatsapp' => 'WhatsApp', 'database' => 'Dalam aplikasi'];

    public const STATUSES = ['queued' => 'Dalam antrean', 'sent' => 'Terkirim', 'failed' => 'Gagal'];

    protected $fillable = ['event', 'channel', 'ticket_id', 'complaint_id', 'user_id', 'recipient', 'subject', 'body', 'status', 'error', 'attempts'];

    protected $casts = ['attempts' => 'integer'];

    protected $attributes = ['attempts' => 0];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'sent' => 'success',
            'queued' => 'warning',
            default => 'danger',
        };
    }
}
