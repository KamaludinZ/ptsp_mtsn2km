<?php

namespace App\Models;

use App\Support\NotificationTemplates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** The wording of one notification on one channel, and whether it is sent. */
class NotificationTemplate extends Model
{
    protected $fillable = ['key', 'channel', 'subject', 'body', 'is_active', 'updated_by'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Keep the wording as it was before every change (riwayat perubahan).
        static::updating(function (NotificationTemplate $template) {
            if ($template->isDirty(['subject', 'body', 'is_active'])) {
                $template->revisions()->create([
                    'subject' => $template->getOriginal('subject'),
                    'body' => $template->getOriginal('body'),
                    'is_active' => (bool) $template->getOriginal('is_active'),
                    'changed_by' => $template->updated_by ?? auth()->id(),
                ]);

                activity('audit')
                    ->causedBy(User::find($template->updated_by ?? auth()->id()))
                    ->performedOn($template)
                    ->withProperties([
                        'changed' => array_keys($template->getDirty()),
                        'is_active' => [(bool) $template->getOriginal('is_active'), (bool) $template->is_active],
                    ])
                    ->log('Mengubah template notifikasi ' . (NotificationTemplates::EVENTS[$template->key] ?? $template->key) . ' (' . $template->channel . ')');
            }
        });
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(NotificationTemplateRevision::class)->latest('created_at')->latest('id');
    }
}
