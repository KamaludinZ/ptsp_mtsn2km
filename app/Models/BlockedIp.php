<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** An IP address refused by CheckBlockedIP: permanent, or until expires_at. */
class BlockedIp extends Model
{
    protected $fillable = ['ip', 'reason', 'blocked_by', 'blocked_at', 'expires_at'];

    protected $casts = [
        'blocked_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    /** The shape the pages and the middleware use (dates as strings). */
    public function toBlockData(): array
    {
        return [
            'reason' => $this->reason,
            'blocked_at' => $this->blocked_at?->toDateTimeString(),
            'blocked_by' => $this->blocked_by,
            'expires_at' => $this->expires_at?->toDateTimeString(),
        ];
    }
}
