<?php

namespace App\Providers;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class AppSettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load settings into config
        $this->loadAppSettings();
    }

    /**
     * Load app settings from database
     */
    private function loadAppSettings(): void
    {
        try {
            $settings = Cache::rememberForever(
                AppSetting::ALL_CACHE_KEY,
                fn () => AppSetting::all(['key', 'value', 'type'])
            );

            foreach ($settings as $setting) {
                // Cast value based on type
                $value = $this->castValue($setting->value, $setting->type);

                config([
                    "app.{$setting->key}" => $value
                ]);
            }

            // Set default values if not exists
            $this->setDefaults();
            $this->syncAppName();
        } catch (\Exception $e) {
            // If database is not ready, set defaults
            $this->setDefaults();
        }
    }

    /**
     * Cast value based on type
     */
    private function castValue($value, string $type)
    {
        return match ($type) {
            'boolean' => (bool) $value,
            'number', 'integer' => is_numeric($value) ? (int) $value : $value,
            'float', 'double' => is_numeric($value) ? (float) $value : $value,
            'array', 'json' => json_decode($value, true) ?? $value,
            default => $value,
        };
    }

    /**
     * Browser titles, mails and Filament all read config('app.name'); keep it
     * equal to the brand name administrators manage in Settings.
     */
    private function syncAppName(): void
    {
        config(['app.name' => app_brand_name()]);
    }

    /**
     * Set default values
     */
    private function setDefaults(): void
    {
        $defaults = [
            'app_name' => 'PTSP MTsN 2 KOTA MALANG',
            'name_full' => 'PTSP MTsN 2 KOTA MALANG',
            'description' => 'Pelayanan Terpadu Satu Pintu MTsN 2 Kota Malang - Layanan cepat, transparan, dan akuntabel sesuai Permen PANRB 15/2014',
            'logo' => null,
            'favicon' => null,
            'contact_phone' => '(0341) 123456',
            'contact_email' => 'info@mtsn2malang.sch.id',
            'contact_address' => 'Jl. Raya Tumpang No. 123, Kota Malang, Jawa Timur',
            'contact_website' => 'www.mtsn2malang.sch.id',
            'social_facebook' => null,
            'social_twitter' => null,
            'social_instagram' => null,
            'social_youtube' => null,
        ];

        foreach ($defaults as $key => $value) {
            if (!config("app.{$key}")) {
                config(["app.{$key}" => $value]);
            }
        }
    }
}