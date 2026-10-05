<?php

namespace App\Support;

use App\Models\NotificationSetting;
use Throwable;

/**
 * Applies the settings saved on Integrasi Notifikasi to the running app, so
 * a change takes effect without editing .env or restarting: on every
 * request, and before every queued job (long-running workers).
 */
class IntegrationRuntime
{
    public static function apply(): void
    {
        try {
            $email = NotificationSetting::where('channel', 'email')->first();
        } catch (Throwable) {
            return; // no database or table yet (install, fresh deploy)
        }

        // Only a saved, enabled channel overrides .env.
        if (! $email?->is_enabled) {
            return;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $email->value('host', config('mail.mailers.smtp.host')),
            'mail.mailers.smtp.port' => (int) $email->value('port', config('mail.mailers.smtp.port')),
            'mail.mailers.smtp.encryption' => ($encryption = $email->value('encryption')) === 'none' ? null : ($encryption ?? config('mail.mailers.smtp.encryption')),
            'mail.mailers.smtp.username' => $email->value('username', config('mail.mailers.smtp.username')),
            'mail.mailers.smtp.password' => $email->value('password', config('mail.mailers.smtp.password')),
            'mail.from.address' => $email->value('from_address', config('mail.from.address')),
            'mail.from.name' => $email->value('from_name', config('mail.from.name')),
        ]);

        // A mailer resolved earlier still holds the old transport.
        app('mail.manager')->purge('smtp');
    }
}
