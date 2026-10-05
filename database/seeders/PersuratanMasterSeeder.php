<?php

namespace Database\Seeders;

use App\Models\PersuratanMaster;
use App\Support\Persuratan;
use App\Support\ServiceDisposition;
use Illuminate\Database\Seeder;

/**
 * The default Master Persuratan choices. Safe to run again: existing
 * entries (and any an officer deactivated) are left as they are.
 */
class PersuratanMasterSeeder extends Seeder
{
    public function run(): void
    {
        $lists = [
            'tujuan_naskah' => array_map(fn ($name) => [null, $name], Persuratan::TUJUAN_NASKAH),
            'jenis_surat' => array_map(fn ($name) => [null, $name], Persuratan::JENIS_SURAT),
            'tembusan' => array_map(fn ($name) => [null, $name], Persuratan::TEMBUSAN),
            'klasifikasi' => array_map(fn ($code, $label) => [$code, trim(explode('—', $label)[1] ?? $label)], array_keys(Persuratan::KLASIFIKASI), Persuratan::KLASIFIKASI),
            'instruksi_disposisi' => array_map(fn ($name) => [null, $name], ServiceDisposition::INSTRUCTIONS),
        ];

        foreach ($lists as $type => $items) {
            foreach (array_values($items) as $i => [$code, $name]) {
                PersuratanMaster::firstOrCreate(['type' => $type, 'nama' => $name], ['kode' => $code, 'sort' => $i, 'is_active' => true]);
            }
        }
    }
}
