<?php

namespace App\Support;

use App\Models\AppSetting;
use Carbon\CarbonInterface;

/**
 * Batas nomor urut tahunan dan kode satker (Pengaturan Aplikasi, token {S}).
 * Nomor surat dirangkai hanya oleh SuratKeluarService::composeNumber.
 */
class SuratKeluarNumber
{
    public const MAX = 9999;

    public const SETTING_KODE_SATKER = 'surat_kode_satker';

    public static function kodeSatker(): string
    {
        return (string) (AppSetting::get(self::SETTING_KODE_SATKER) ?: 'MTsN2KM');
    }
}
