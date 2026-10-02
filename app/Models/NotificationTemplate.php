<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** The wording of one notification on one channel, and whether it is sent. */
class NotificationTemplate extends Model
{
    protected $fillable = ['key', 'channel', 'subject', 'body', 'is_active', 'updated_by'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
