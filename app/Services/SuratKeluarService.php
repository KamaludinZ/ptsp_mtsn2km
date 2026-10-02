<?php

namespace App\Services;

use App\Exceptions\TicketActionException;
use App\Models\SuratKeluar;
use App\Models\SuratKeluarBatch;
use App\Models\User;
use App\Support\SuratKeluarNumber;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Penomoran surat keluar: sequential numbers 1-9999 that restart every year,
 * reserved one or several at a time. The yearly counter row is locked while
 * numbers are handed out, so two officers never receive the same number.
 */
class SuratKeluarService
{
    public const MAX_PER_REQUEST = 50;

    /**
     * Reserve $count consecutive numbers for the year of $tanggal.
     *
     * @param  array<string, mixed>  $data  letter fields shared by every reserved number
     * @return Collection<int, SuratKeluar>
     */
    public function reserve(int $count, CarbonInterface $tanggal, User $pembuat, array $data = []): Collection
    {
        if ($count < 1 || $count > self::MAX_PER_REQUEST) {
            throw new TicketActionException('Jumlah nomor harus antara 1 dan ' . self::MAX_PER_REQUEST . '.');
        }

        $year = (int) $tanggal->format('Y');

        return DB::transaction(function () use ($count, $tanggal, $pembuat, $data, $year) {
            DB::table('surat_sequences')->insertOrIgnore(['tahun' => $year, 'last_number' => 0, 'created_at' => now(), 'updated_at' => now()]);
            $last = (int) DB::table('surat_sequences')->where('tahun', $year)->lockForUpdate()->value('last_number');

            if ($last + $count > SuratKeluarNumber::MAX) {
                throw new TicketActionException('Nomor surat tahun ' . $year . ' tinggal ' . (SuratKeluarNumber::MAX - $last) . '.');
            }

            $batch = SuratKeluarBatch::create([
                'tahun' => $year,
                'jumlah_diminta' => $count,
                'nomor_awal' => $last + 1,
                'nomor_akhir' => $last + $count,
                'created_by' => $pembuat->id,
            ]);

            $letters = collect(range($last + 1, $last + $count))->map(fn (int $urut) => SuratKeluar::create([
                ...array_intersect_key($data, array_flip(['tujuan_surat', 'perihal', 'jenis_surat', 'klasifikasi', 'lampiran', 'tembusan', 'keterangan'])),
                'tahun' => $year,
                'nomor_urut' => $urut,
                'nomor_surat' => SuratKeluarNumber::format($urut, $tanggal, $data['klasifikasi'] ?? null),
                'tanggal_surat' => $tanggal,
                'pembuat_id' => $pembuat->id,
                'batch_id' => $batch->id,
            ]));

            DB::table('surat_sequences')->where('tahun', $year)->update(['last_number' => $last + $count, 'updated_at' => now()]);

            return $letters;
        });
    }

    /** The number the next request would receive for $year. */
    public function nextNumber(int $year): int
    {
        return (int) DB::table('surat_sequences')->where('tahun', $year)->value('last_number') + 1;
    }
}
