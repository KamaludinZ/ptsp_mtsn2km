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

    /**
     * Sequence numbers written as runs: [3, 7, 8, 9] → "3, 7–9".
     *
     * @param  iterable<int>  $numbers
     */
    public static function ranges(iterable $numbers): string
    {
        $numbers = collect($numbers)->map(fn ($n) => (int) $n)->unique()->sort()->values();
        $runs = [];

        foreach ($numbers as $n) {
            $last = array_key_last($runs);
            if ($last !== null && $runs[$last][1] === $n - 1) {
                $runs[$last][1] = $n;
            } else {
                $runs[] = [$n, $n];
            }
        }

        return collect($runs)->map(fn (array $run) => $run[0] === $run[1] ? (string) $run[0] : "{$run[0]}–{$run[1]}")->join(', ');
    }
}
