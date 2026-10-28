<?php

namespace Database\Seeders;

use App\Models\SuratKeputusan;
use App\Models\User;
use App\Support\Persuratan;
use Illuminate\Database\Seeder;

/** Demo SK numbers for this year (non-production only). */
class SuratKeputusanSeeder extends Seeder
{
    public function run(): void
    {
        $year = now()->year;
        $maker = User::role('back_office')->first() ?? User::role('admin')->first();
        [$kepegawaian, $tugas, $panitia, $tim, $keuangan] = Persuratan::JENIS_SK;

        $sk = [
            ['Pengangkatan wali kelas tahun pelajaran berjalan', $kepegawaian, 'Kepala Tata Usaha', null],
            ['Pembagian tugas mengajar dan tugas tambahan guru semester ganjil', $tugas, 'Waka Kurikulum', 'Lampiran daftar pembagian tugas 4 halaman.'],
            ['Panitia penerimaan peserta didik baru', $panitia, 'Waka Kesiswaan', null],
            ['Pembentukan tim pengembang kurikulum madrasah', $tim, 'Waka Kurikulum', 'Berlaku satu tahun pelajaran.'],
            ['Penetapan bendahara pengeluaran dan pengelola BOS', $keuangan, 'Kepala Tata Usaha', null],
            ['Panitia peringatan hari besar Islam', $panitia, 'Waka Kesiswaan', null],
        ];

        foreach ($sk as $i => [$perihal, $jenis, $peminta, $keterangan]) {
            $urut = $i + 1;

            SuratKeputusan::updateOrCreate(['tahun' => $year, 'nomor_urut' => $urut], [
                'nomor_sk' => SuratKeputusan::composeNumber($urut, $year),
                'tanggal_surat' => now()->startOfYear()->addDays($i * 23),
                'perihal' => $perihal,
                'jenis_sk' => $jenis,
                'nama_peminta' => $peminta,
                'keterangan' => $keterangan,
                'pembuat_id' => $maker?->id,
                'peran_pembuat' => $maker ? 'back_office' : null,
            ]);
        }
    }
}
