<?php

namespace App\Support;

use App\Models\AppSetting;

/**
 * Civitas (internal) sign-up, set by the admin on the roles page: one switch
 * opens or closes the form, another decides whether the shared registration
 * code is asked for. With the code required, it proves the person belongs to
 * the school; either way they then pick their own account type.
 */
class CivitasRegistration
{
    public const SETTING_KEY = 'civitas_registration_code';

    public const OPEN_KEY = 'civitas_registration_open';

    public const CODE_REQUIRED_KEY = 'civitas_registration_code_required';

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
        $code = trim((string) self::value(self::SETTING_KEY));

        return $code === '' ? null : $code;
    }

    public static function setCode(?string $code): void
    {
        AppSetting::updateOrCreate(['key' => self::SETTING_KEY], [
            'value' => trim((string) $code),
            'type' => 'text',
            'category' => 'registration',
            'display_name' => 'Kode registrasi civitas',
            'description' => 'Kode yang harus dimasukkan civitas saat mendaftar akun (bila penggunaan kode diaktifkan).',
        ]);
    }

    /** Whether the admin opened the form. Before the switch existed, a set code meant open. */
    public static function formEnabled(): bool
    {
        $value = self::value(self::OPEN_KEY);

        return $value === null ? self::code() !== null : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public static function setFormEnabled(bool $open): void
    {
        self::setFlag(self::OPEN_KEY, $open, 'Pendaftaran civitas dibuka', 'Buka atau tutup form pendaftaran akun civitas internal.');
    }

    public static function codeRequired(): bool
    {
        $value = self::value(self::CODE_REQUIRED_KEY);

        return $value === null || filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public static function setCodeRequired(bool $required): void
    {
        self::setFlag(self::CODE_REQUIRED_KEY, $required, 'Wajib kode registrasi civitas', 'Civitas harus memasukkan kode registrasi saat mendaftar.');
    }

    /** Civitas can sign up: the form is open and, when a code is required, one is set. */
    public static function isOpen(): bool
    {
        return self::formEnabled() && (! self::codeRequired() || self::code() !== null);
    }

    public static function matches(?string $input): bool
    {
        $code = self::code();

        return $code !== null && $input !== null && hash_equals($code, trim($input));
    }

    private static function value(string $key): ?string
    {
        return AppSetting::where('key', $key)->value('value');
    }

    private static function setFlag(string $key, bool $value, string $name, string $description): void
    {
        AppSetting::updateOrCreate(['key' => $key], [
            'value' => $value ? 'true' : 'false',
            'type' => 'boolean',
            'category' => 'registration',
            'display_name' => $name,
            'description' => $description,
        ]);
    }
}
