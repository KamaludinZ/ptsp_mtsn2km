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

    /** The secret each channel holds. */
    public const CHANNEL_SECRETS = ['email' => ['password'], 'whatsapp' => ['api_token']];

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

    /**
     * Save a channel's settings. A blank secret keeps the stored one, so a
     * form or API client never has to send credentials back.
     */
    public static function store(string $channel, bool $enabled, array $values, ?int $userId = null): self
    {
        $setting = static::for($channel);
        $values = collect($values)->except('is_enabled');

        $setting->fill([
            'is_enabled' => $enabled,
            'config' => array_merge(
                $setting->config ?? [],
                $values->except(self::SECRETS)->all(),
                $values->only(self::SECRETS)->filter(fn ($value) => filled($value))->all(),
            ),
            'updated_by' => $userId,
        ])->save();

        return $setting;
    }

    /** The settings without secrets, plus whether each secret is set. */
    public function publicConfig(): array
    {
        $config = collect($this->config ?? [])->except(self::SECRETS)->all();

        foreach (self::CHANNEL_SECRETS[$this->channel] ?? [] as $secret) {
            $config[$secret . '_tersimpan'] = $this->hasSecret($secret);
        }

        return $config;
    }
}
