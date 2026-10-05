<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A message sent from the public contact page. */
class ContactMessage extends Model
{
    protected $fillable = ['name', 'email', 'subject', 'message', 'ip_address', 'read_at', 'read_by'];

    protected $casts = ['read_at' => 'datetime'];

    public function reader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'read_by');
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}
