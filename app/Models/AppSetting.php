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
    ];

    protected $casts = [
        'validation_rules' => 'array',
    ];

    public const ALL_CACHE_KEY = 'app_settings_all';

    protected static function booted(): void
    {
        // Admin edits (Filament or code) must show up on the next request,
        // so every write drops the cached copies of that setting.
        $flush = function (AppSetting $setting): void {
            Cache::forget(self::ALL_CACHE_KEY);
            Cache::forget("app_setting_{$setting->key}");
            Cache::forget("app_settings_category_{$setting->category}");
        };

        static::saved($flush);
        static::deleted($flush);
    }

    /**
     * Get setting value by key with caching
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("app_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set setting value
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        // Clear cache
        Cache::forget("app_setting_{$key}");
    }

    /**
     * Get all settings by category
     */
    public static function getByCategory(string $category): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember("app_settings_category_{$category}", 3600, function () use ($category) {
            return static::where('category', $category)->get();
        });
    }

    /**
     * Clear all cache
     */
    public static function clearCache(): void
    {
        Cache::flush();
    }

    /**
     * Boot method to clear cache on changes
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saved(function () {
            static::clearCache();
        });

        static::deleted(function () {
            static::clearCache();
        });
    }
}