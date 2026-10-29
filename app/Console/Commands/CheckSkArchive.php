<?php

namespace App\Console\Commands;

use App\Services\SuratKeputusanService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/** Periksa arsip scan SK: every archived scan is still on the private disk and unchanged since upload. */
class CheckSkArchive extends Command
{
    protected $signature = 'sk:periksa-arsip {--tahun= : Hanya SK tahun ini (bawaan: semua tahun)}';

    protected $description = 'Periksa berkas scan SK di arsip: hilang, berubah, atau belum bersidik';

    public function handle(): int
    {
        $year = $this->option('tahun') !== null ? (int) $this->option('tahun') : null;
        $result = SuratKeputusanService::verifyArchive($year);

        $this->info("{$result['diperiksa']} scan diperiksa, {$result['utuh']} utuh.");
        if ($result['masalah'] === []) {
            return self::SUCCESS;
        }

        $this->table(['Nomor SK', 'Masalah'], array_map(fn (array $p) => [$p['nomor_sk'], $p['keterangan']], $result['masalah']));
        Log::warning('Arsip scan SK bermasalah', ['masalah' => $result['masalah']]);

        return self::FAILURE;
    }
}
