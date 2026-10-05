<?php

namespace App\Support;

use App\Filament\Pages\System\Settings;
use App\Models\AppSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Writes Pengaturan Aplikasi (Settings::FIELDS) for the settings screen and
 * the /api/pengaturan endpoints alike: values are normalised, only real
 * changes are saved, and every save that changes something is audited.
 */
class SettingsStore
{
    /** Where uploaded logos live on the public disk (setting value: "storage/branding/…"). */
    public const LOGO_DIRECTORY = 'branding';

    /**
     * @param  array<string, mixed>  $values  setting key => new value (null or '' clears it)
     * @return list<string> labels of the settings that changed
     */
    public static function save(array $values): array
    {
        $values = array_intersect_key($values, Settings::FIELDS);
        $before = AppSetting::whereIn('key', array_keys($values))->pluck('value', 'key');
        $changed = [];

        DB::transaction(function () use ($values, $before, &$changed) {
            foreach ($values as $key => $raw) {
                [$category, $type, $label] = Settings::FIELDS[$key];
                $value = self::normalise($type, $raw);

                // An empty value for a setting that does not exist yet is no change.
                if (! $before->has($key) && ($value === null || $value === 'false')) {
                    continue;
                }
                if (($before[$key] ?? null) === $value) {
                    continue;
                }

                $changed[] = $label;
                AppSetting::updateOrCreate(['key' => $key], [
                    'value' => $value,
                    'type' => $type,
                    'category' => $category,
                    'display_name' => AppSetting::where('key', $key)->value('display_name') ?? $label,
                ]);
            }
        });

        if ($changed) {
            activity('audit')->causedBy(auth()->user())->withProperties(['diubah' => $changed])
                ->log('Mengubah pengaturan aplikasi: ' . implode(', ', $changed));
        }

        return $changed;
    }

    public static function normalise(string $type, mixed $value): ?string
    {
        if ($type === 'boolean') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        }

        return filled($value) ? trim((string) $value) : null;
    }

    /** Current values of the given keys (null when not set). */
    public static function values(array $keys): array
    {
        return collect($keys)->mapWithKeys(fn (string $key) => [$key => AppSetting::get($key)])->all();
    }

    /** A logo uploaded earlier through the settings (never a bundled file such as images/logo.png). */
    public static function deleteUploadedLogo(?string $value): void
    {
        if ($value && str_starts_with($value, 'storage/' . self::LOGO_DIRECTORY . '/')) {
            Storage::disk('public')->delete(substr($value, strlen('storage/')));
        }
    }
}
