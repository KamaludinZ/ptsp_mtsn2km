<?php

namespace App\Support;

use App\Models\AppSetting;
use Carbon\CarbonInterface;

/**
 * Composes the official outgoing-letter number, e.g. "B-12/MTsN2KM/PP.00/10/2026":
 * sequence, unit code (setting "surat_kode_satker"), classification, month, year.
 */
class SuratKeluarNumber
{
    public const MAX = 9999;

    public const SETTING_KODE_SATKER = 'surat_kode_satker';

    public static function kodeSatker(): string
    {
        return (string) (AppSetting::get(self::SETTING_KODE_SATKER) ?: 'MTsN2KM');
    }

    public static function format(int $urut, CarbonInterface $tanggal, ?string $klasifikasi = null): string
    {
        return collect([
            'B-' . $urut,
            self::kodeSatker(),
            filled($klasifikasi) ? trim($klasifikasi) : null,
            $tanggal->format('m'),
            $tanggal->format('Y'),
        ])->filter()->join('/');
    }
}
