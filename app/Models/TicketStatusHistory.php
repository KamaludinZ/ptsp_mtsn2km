<?php

namespace App\Models;

use App\Support\TicketLabels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * One status a request entered (riwayat status permohonan). Written by the
 * tickets_record_status database trigger; read-only in the application.
 */
class TicketStatusHistory extends Model
{
    public $timestamps = false;

    protected $guarded = ['*'];

    protected $casts = [
        'changed_at' => 'datetime',
        'previous_duration_seconds' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(fn () => throw new LogicException('Riwayat status dicatat otomatis oleh database.'));
        static::updating(fn () => throw new LogicException('Riwayat status tidak dapat diubah.'));
        static::deleting(fn () => throw new LogicException('Riwayat status tidak dapat dihapus.'));
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /** How long the request stayed in from_status, e.g. "2 hari 3 jam"; null for the first status. */
    public function previousDuration(): ?string
    {
        return $this->previous_duration_seconds === null
            ? null
            : \App\Support\TicketProgress::duration($this->changed_at->copy()->subSeconds($this->previous_duration_seconds), $this->changed_at);
    }

    public function scopeEntered($query, string $status)
    {
        return $query->where('to_status', $status);
    }

    public function scopeBetween($query, $from = null, $until = null)
    {
        return $query
            ->when($from, fn ($q) => $q->where('changed_at', '>=', $from))
            ->when($until, fn ($q) => $q->where('changed_at', '<=', $until));
    }

    public function label(): string
    {
        return $this->from_status
            ? TicketLabels::status($this->from_status) . ' → ' . TicketLabels::status($this->to_status)
            : TicketLabels::status($this->to_status);
    }
}
