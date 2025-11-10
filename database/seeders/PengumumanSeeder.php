<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pengumuman;

class PengumumanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate table to prevent duplicates
        Pengumuman::truncate();
        
        // Create sample announcements
        Pengumuman::create([
            'title' => 'Pengumuman Hasil Seleksi PPDB Tahun Ajaran 2025/2026',
            'content' => 'Diberitahukan kepada calon peserta didik baru bahwa hasil seleksi Penerimaan Peserta Didik Baru (PPDB) Tahun Ajaran 2025/2026 telah dapat diakses melalui website resmi sekolah. Calon peserta didik dapat mencetak bukti seleksi dan mengikuti tahapan selanjutnya sesuai jadwal yang telah ditentukan. Segala informasi lebih lanjut dapat diakses pada halaman PPDB di website sekolah atau menghubungi panitia di nomor yang telah disediakan.',
            'category' => 'akademik',
            'publish_date' => now()->subDays(2),
            'end_date' => now()->addDays(30),
            'is_active' => true,
            'author' => 'Admin MTsN 2 Kota Malang',
        ]);

        Pengumuman::create([
            'title' => 'Jadwal Penilaian Akhir Semester Genap Tahun Pelajaran 2024/2025',
            'content' => 'Diberitahukan kepada seluruh peserta didik kelas VII dan VIII bahwa Jadwal Penilaian Akhir Semester (PAS) Genap Tahun Pelajaran 2024/2025 akan dilaksanakan mulai tanggal 15 Juni 2025 hingga 25 Juni 2025. Materi ujian mencakup seluruh kompetensi dasar yang telah diajarkan selama semester genap. Peserta didik diharapkan mempersiapkan diri dengan baik dan memperhatikan jadwal ujian yang telah disusun oleh masing-masing guru mata pelajaran. Perlengkapan ujian wajib dibawa sesuai dengan ketentuan yang telah ditentukan.',
            'category' => 'akademik',
            'publish_date' => now()->subDays(5),
            'end_date' => now()->addDays(10),
            'is_active' => true,
            'author' => 'Kepala Sekolah MTsN 2 Kota Malang',
        ]);

        Pengumuman::create([
            'title' => 'Pendaftaran Program Kelas Olahraga Tahun 2025',
            'content' => 'Sekolah membuka program Kelas Olahraga (KO) untuk Tahun Ajaran 2025/2026. Program ini ditujukan kepada peserta didik yang memiliki minat dan bakat di bidang olahraga. Pendaftaran dibuka mulai tanggal 1 April 2025 hingga 30 April 2025. Calon peserta wajib mengikuti tes keterampilan dan wawancara. Persyaratan administrasi dapat diunduh melalui website sekolah. Informasi lebih lanjut dapat diperoleh di bagian Tata Usaha atau melalui hotline yang tersedia.',
            'category' => 'kegiatan',
            'publish_date' => now()->subDays(7),
            'end_date' => now()->addDays(20),
            'is_active' => true,
            'author' => 'Wakil Kurikulum MTsN 2 Kota Malang',
        ]);

        Pengumuman::create([
            'title' => 'Pengajuan Pindah Sekolah T.A 2025/2026',
            'content' => 'Bagi peserta didik yang berkeinginan untuk pindah sekolah pada Tahun Ajaran 2025/2026, dapat mengajukan permohonan secara tertulis ke bagian Tata Usaha. Persyaratan administrasi yang harus dilengkapi antara lain fotokopi rapor semester terakhir, surat keterangan pindah domisili dari lurah/setara (jika pindah wilayah), formulir permohonan yang dapat diambil di Tata Usaha. Batas akhir pengajuan permohonan adalah tanggal 15 Juni 2025. Proses verifikasi dan persetujuan akan dilakukan maksimal 7 hari kerja setelah berkas dinyatakan lengkap.',
            'category' => 'administrasi',
            'publish_date' => now()->subDays(3),
            'end_date' => now()->addDays(25),
            'is_active' => true,
            'author' => 'TU MTsN 2 Kota Malang',
        ]);

        Pengumuman::create([
            'title' => 'Peringatan Isra\' Mi\'raj Nabi Muhammad SAW 1446 H',
            'content' => 'Sekolah akan menyelenggarakan peringatan Isra\' Mi\'raj Nabi Muhammad SAW 1446 H yang insya Allah akan dilaksanakan pada hari Jumat, 28 Maret 2025 bertempat di halaman utama MTsN 2 Kota Malang. Seluruh peserta didik, guru, karyawan, dan orang tua/wali murid diharapkan hadir dalam acara ini. Kegiatan akan diisi dengan ceramah agama, shalawat, dan hiburan religi. Mohon kehadiran dan keterlibatan aktif dari seluruh warga sekolah. Acara dimulai pukul 08.00 WIB hingga selesai.',
            'category' => 'kegiatan',
            'publish_date' => now()->subDays(10),
            'end_date' => now()->addDays(15),
            'is_active' => true,
            'author' => 'Panitia Kegiatan MTsN 2 Kota Malang',
        ]);
    }
}