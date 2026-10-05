<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use App\Support\NotificationTemplates;
use Illuminate\Database\Seeder;

/**
 * The default notification templates, one per event and channel. Safe to
 * run again: templates an admin already edited are left untouched; only
 * missing ones (e.g. a newly added event) are created.
 */
class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach (NotificationTemplates::defaults() as $key => $template) {
            foreach (['email', 'whatsapp'] as $channel) {
                NotificationTemplate::firstOrCreate(['key' => $key, 'channel' => $channel], [
                    'subject' => $channel === 'email' ? $template['subject'] : null,
                    'body' => $template['body'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
