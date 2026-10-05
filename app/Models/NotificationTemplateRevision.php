<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A notification template as it was before one change. */
class NotificationTemplateRevision extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['notification_template_id', 'subject', 'body', 'is_active', 'changed_by'];

    protected $casts = ['is_active' => 'boolean'];

    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'notification_template_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
