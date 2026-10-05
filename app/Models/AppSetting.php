<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'category',
        'display_name',
        'description',
        'validation_rules',
        'updated_by',
    ];

    protected $casts = [
        'validation_rules' => 'array',
    ];

    public const ALL_CACHE_KEY = 'app_settings_all';

    /** Value types the settings forms know (app_settings_type_check). */
    public const TYPES = ['text', 'textarea', 'email', 'url', 'boolean', 'select', 'image', 'number'];

    protected static function booted(): void
    {
        // Admin edits (Filament or code) must show up on the next request,
        // so every write drops the cached copies of that setting.
        $flush = function (AppSetting $setting): void {
            Cache::forget(self::ALL_CACHE_KEY);
            Cache::forget("app_setting_{$setting->key}");
            Cache::forget("app_settings_category_{$setting->category}");
        };

        static::saving(function (AppSetting $setting): void {
            $setting->key = strtolower(trim((string) $setting->key));
            if ($setting->isDirty('value') && auth()->id()) {
                $setting->updated_by = auth()->id();
            }
        });

        static::saved($flush);
        static::deleted($flush);
    }

    public function updatedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * A setting's value, or the default when it isn't set. Reads the one
     * cached copy of all settings (also used to fill config at boot), so a
     * missing key costs no query and every caller gets its own default.
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::cached()->firstWhere('key', $key);

        return $setting?->value ?? $default;
    }

    /** All settings (key, value, type), cached until one changes. */
    public static function cached(): \Illuminate\Support\Collection
    {
        return Cache::rememberForever(self::ALL_CACHE_KEY, fn () => static::all(['key', 'value', 'type']));
    }

    /** Set a value (creating the setting when needed); caches are dropped by the model events. */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /** All settings of one category. */
    public static function getByCategory(string $category): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember("app_settings_category_{$category}", 3600, fn () => static::where('category', $category)->get());
    }

    /** Drop the cached settings only (never the whole application cache: rate limits, lockouts). */
    public static function clearCache(): void
    {
        Cache::forget(self::ALL_CACHE_KEY);
        foreach (static::query()->distinct()->pluck('category') as $category) {
            Cache::forget("app_settings_category_{$category}");
        }
    }
}
