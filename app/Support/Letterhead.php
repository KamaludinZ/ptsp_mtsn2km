<?php

namespace App\Support;

use App\Models\AppSetting;
use App\Models\User;

/**
 * Kop instansi for printed documents: the ministry lines, the madrasah's
 * full name, contact line and logo (from Pengaturan Aplikasi), plus the
 * headmaster who signs reports.
 */
class Letterhead
{
    /**
     * @return array{lines: array<int, string>, name: string, contact: string, logo: ?string}
     */
    public static function data(): array
    {
        $setting = fn (string $key, ?string $default = null) => AppSetting::get($key, $default) ?: $default;

        $contact = array_filter([
            $setting('contact_address'),
            ($phone = $setting('contact_phone')) ? 'Telp. ' . $phone : null,
            ($email = $setting('contact_email')) ? 'Email: ' . $email : null,
            $setting('contact_website'),
        ]);
        $logo = $setting('app_logo');

        return [
            'lines' => array_values(array_filter([
                $setting('letterhead_line_1', 'Kementerian Agama Republik Indonesia'),
                $setting('letterhead_line_2', 'Kantor Kementerian Agama Kota Malang'),
            ])),
            'name' => $setting('app_name_full', 'Madrasah Tsanawiyah Negeri 2 Kota Malang'),
            'contact' => implode(' · ', $contact),
            'logo' => $logo ? (str_starts_with($logo, 'http') ? $logo : asset($logo)) : null,
        ];
    }

    /** Kepala Madrasah who signs the report (name only when unknown). */
    public static function signatory(): array
    {
        $head = User::role('kepala_sekolah')->where('is_active', true)->orderBy('id')->first();
        $name = AppSetting::get('headmaster_name') ?: $head?->name;
        $nip = AppSetting::get('headmaster_nip');

        return ['title' => 'Kepala Madrasah', 'name' => $name, 'nip' => $nip ? preg_replace('/\s+/', ' ', trim($nip)) : null];
    }
}
