<?php

namespace App\Support;

use App\Models\AppSetting;

/**
 * Civitas (internal) sign-up: one shared registration code, set by the admin
 * on the roles page, proves the person belongs to the school; they then pick
 * their own account type. An empty code closes civitas registration.
 */
class CivitasRegistration
{
    public const SETTING_KEY = 'civitas_registration_code';

    public const USER_TYPES = [
        'siswa' => 'Siswa',
        'guru' => 'Guru',
        'pegawai' => 'Pegawai',
        'walimurid' => 'Wali Murid',
        'alumni' => 'Alumni',
        'instansi' => 'Instansi/Perusahaan',
    ];

    public static function code(): ?string
    {
        $code = trim((string) AppSetting::where('key', self::SETTING_KEY)->value('value'));

        return $code === '' ? null : $code;
    }

    public static function setCode(?string $code): void
    {
        AppSetting::updateOrCreate(['key' => self::SETTING_KEY], [
            'value' => trim((string) $code),
            'type' => 'text',
            'category' => 'registration',
            'display_name' => 'Kode registrasi civitas',
            'description' => 'Kode yang harus dimasukkan civitas saat mendaftar akun. Kosong = pendaftaran civitas ditutup.',
        ]);
    }

    public static function matches(?string $input): bool
    {
        $code = self::code();

        return $code !== null && $input !== null && hash_equals($code, trim($input));
    }
}
