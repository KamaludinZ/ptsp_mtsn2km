<?php

namespace Database\Seeders;

use App\Models\SuratKeluar;
use App\Models\User;
use App\Support\SuratKeluarNumber;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Demo outgoing letters for this year (non-production only). */
class SuratKeluarSeeder extends Seeder
{
    public function run(): void
    {
        $year = now()->year;
        $maker = User::role('back_office')->first() ?? User::role('admin')->first();

        $letters = [
            ['Dinas Pendidikan Kota Malang', 'Permohonan data siswa penerima beasiswa', 'Surat Dinas', 'PP.00'],
            ['Orang tua/wali siswa kelas IX', 'Undangan rapat persiapan ujian madrasah', 'Undangan', 'PP.00'],
            ['Kantor Kementerian Agama Kota Malang', 'Laporan kegiatan bulan berjalan', 'Laporan', 'OT.00'],
            ['Puskesmas Arjuno', 'Permohonan pemeriksaan kesehatan siswa', 'Surat Dinas', 'KS.00'],
            ['SMA Negeri 1 Malang', 'Keterangan pindah sekolah', 'Keterangan', 'PP.00'],
        ];

        foreach ($letters as $i => [$tujuan, $perihal, $jenis, $klasifikasi]) {
            $urut = $i + 1;
            $tanggal = now()->startOfYear()->addDays($i * 17);

            SuratKeluar::updateOrCreate(['tahun' => $year, 'nomor_urut' => $urut], [
                'nomor_surat' => SuratKeluarNumber::format($urut, $tanggal, $klasifikasi),
                'tanggal_surat' => $tanggal,
                'tujuan_surat' => $tujuan,
                'perihal' => $perihal,
                'jenis_surat' => $jenis,
                'klasifikasi' => $klasifikasi,
                'pembuat_id' => $maker?->id,
            ]);
        }

        DB::table('surat_sequences')->updateOrInsert(['tahun' => $year], ['last_number' => count($letters), 'updated_at' => now()]);
    }
}
