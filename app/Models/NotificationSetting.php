<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Integration settings of one notification channel. The gateway config
 * holds credentials and is encrypted at rest.
 */
class NotificationSetting extends Model
{
    public const CHANNELS = [
        'email' => 'Email',
        'whatsapp' => 'WhatsApp',
    ];

    /** Config keys that are secrets: never sent back to the browser. */
    public const SECRETS = ['password', 'api_token'];

    protected $fillable = ['channel', 'is_enabled', 'config', 'updated_by'];

    protected $casts = [
        'is_enabled' => 'boolean',
        'config' => 'encrypted:array',
    ];

    public static function for(string $channel): self
    {
        return static::firstOrNew(['channel' => $channel], ['is_enabled' => false, 'config' => []]);
    }

    public function value(string $key, mixed $default = null): mixed
    {
        return ($this->config ?? [])[$key] ?? $default;
    }

    public function hasSecret(string $key): bool
    {
        return filled($this->value($key));
    }
}
