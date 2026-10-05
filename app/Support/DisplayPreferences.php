<?php

namespace App\Support;

use App\Models\User;

/**
 * Preferensi tampilan: theme mode (follow the system, light, dark) and text
 * size, kept on the user and applied in both panels.
 */
class DisplayPreferences
{
    public const MODES = ['system' => 'Ikuti perangkat', 'light' => 'Terang', 'dark' => 'Gelap'];

    public const SIZES = ['normal' => 'Normal', 'large' => 'Besar', 'xlarge' => 'Lebih besar'];

    public const SIZE_PERCENT = ['normal' => 100, 'large' => 112.5, 'xlarge' => 125];

    /** @return array{mode: string, size: string, updated_at: ?int} */
    public static function for(?User $user): array
    {
        $saved = (array) ($user?->preferences ?? []);

        return [
            'mode' => array_key_exists($saved['mode'] ?? '', self::MODES) ? $saved['mode'] : 'system',
            'size' => array_key_exists($saved['size'] ?? '', self::SIZES) ? $saved['size'] : 'normal',
            'updated_at' => isset($saved['updated_at']) ? (int) $saved['updated_at'] : null,
        ];
    }

    public static function save(User $user, string $mode, string $size): void
    {
        $user->forceFill(['preferences' => array_merge((array) $user->preferences, [
            'mode' => array_key_exists($mode, self::MODES) ? $mode : 'system',
            'size' => array_key_exists($size, self::SIZES) ? $size : 'normal',
            'updated_at' => now()->getTimestamp(),
        ])])->save();
    }

    /**
     * Head snippet for the panels: the text size, and the saved theme mode the
     * first time after it was changed (Filament's own theme switcher keeps
     * working in between).
     */
    public static function headHtml(?User $user): string
    {
        if (! $user) {
            return '';
        }
        $p = self::for($user);
        $percent = self::SIZE_PERCENT[$p['size']];
        $style = $percent === 100 ? '' : "<style>html{font-size:{$percent}%}</style>";
        $script = $p['updated_at'] === null ? '' : sprintf(
            '<script>try{if(localStorage.getItem("display-pref-at")!==%1$s){localStorage.setItem("theme",%2$s);localStorage.setItem("display-pref-at",%1$s)}}catch(e){}</script>',
            json_encode((string) $p['updated_at']),
            json_encode($p['mode']),
        );

        return $style . $script;
    }
}
