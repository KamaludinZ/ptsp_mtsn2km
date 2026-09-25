<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class SchoolServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Services are owned by the first admin account (IDs differ per database)
        $adminId = \App\Models\User::role('admin')->orderBy('id')->value('id');

        DB::transaction(function () use ($adminId) {
            // Additional school services with different modes
            $services = [
                [
                    'name' => 'Pendaftaran Siswa Baru',
                    'code' => 'PSB-011',
                    'description' => 'Pelayanan pendaftaran untuk siswa baru di tahun ajaran baru',
                    'mode' => 'hybrid',
                    'requirements' => '1. Formulir pendaftaran 2. Fotokopi akta kelahiran 3. Fotokopi Kartu Keluarga 4. Ijazah dan SKHUN',
                    'mechanism' => '1. Pendaftaran online atau offline 2. Verifikasi berkas 3. Wawancara (jika diperlukan) 4. Pengumuman hasil seleksi',
                    'processing_time' => '3-5 hari kerja',
                    'fee' => 0,
                    'product' => 'Bukti pendaftaran dan formulir persetujuan sekolah',
                    'complaint_handling' => 'Layanan pengaduan via email: mtsnmalang2adm@gmail.com atau nomor WA: 0851 8337 5008',
                    'user_types_allowed' => ['walimurid', 'umum'],
                    'is_active' => true,
                    'created_by' => $adminId,
                ],
                [
                    'name' => 'Permohonan Cuti Sakit',
                    'code' => 'PCS-012',
                    'description' => 'Permohonan cuti karena sakit untuk guru dan staff',
                    'mode' => 'online',
                    'requirements' => '1. Surat keterangan dokter 2. Formulir permohonan cuti 3. Bukti pemeriksaan medis',
                    'mechanism' => '1. Pengajuan online melalui sistem 2. Verifikasi surat keterangan dokter 3. Persetujuan kepala sekolah 4. Update data kehadiran',
                    'processing_time' => '1-2 hari kerja',
                    'fee' => 0,
                    'product' => 'Surat persetujuan cuti sakit yang telah disahkan',
                    'complaint_handling' => 'Layanan pengaduan via email: mtsnmalang2adm@gmail.com atau nomor WA: 0851 8337 5008',
                    'user_types_allowed' => ['pegawai'],
                    'is_active' => true,
                    'created_by' => $adminId,
                ],
                [
                    'name' => 'Bimbingan Konseling Offline',
                    'code' => 'BKO-013',
                    'description' => 'Layanan bimbingan konseling tatap muka untuk siswa',
                    'mode' => 'offline',
                    'requirements' => '1. Pendaftaran terlebih dahulu (opsional) 2. Identitas siswa 3. Permasalahan yang ingin didiskusikan',
                    'mechanism' => '1. Pendaftaran di ruang BK 2. Penjadwalan sesi 3. Pelaksanaan bimbingan 4. Evaluasi dan tindak lanjut',
                    'processing_time' => 'Sesuai jadwal',
                    'fee' => 0,
                    'product' => 'Rekomendasi dan solusi permasalahan siswa serta dokumentasi konseling',
                    'complaint_handling' => 'Layanan pengaduan via email: mtsnmalang2adm@gmail.com atau nomor WA: 0851 8337 5008',
                    'user_types_allowed' => ['siswa', 'walimurid'],
                    'is_active' => true,
                    'created_by' => $adminId,
                ],
                [
                    'name' => 'Pengajuan Beasiswa Online',
                    'code' => 'PBO-014',
                    'description' => 'Formulir pengajuan beasiswa secara online',
                    'mode' => 'online',
                    'requirements' => '1. Formulir pengajuan online 2. Bukti prestasi akademik/non-akademik 3. Surat keterangan tidak mampu (jika beasiswa prestasi)',
                    'mechanism' => '1. Pengisian formulir online 2. Upload dokumen pendukung 3. Verifikasi data 4. Seleksi dan pengumuman',
                    'processing_time' => '5-7 hari kerja',
                    'fee' => 0,
                    'product' => 'Status pengajuan beasiswa dan surat pemberitahuan hasil seleksi',
                    'complaint_handling' => 'Layanan pengaduan via email: mtsnmalang2adm@gmail.com atau nomor WA: 0851 8337 5008',
                    'user_types_allowed' => ['siswa'],
                    'is_active' => true,
                    'created_by' => $adminId,
                ],
                [
                    'name' => 'Permohonan SKCK Pelajar',
                    'code' => 'SKCK-015',
                    'description' => 'Layanan permohonan SKCK untuk pelajar',
                    'mode' => 'hybrid',
                    'requirements' => '1. Formulir permohonan 2. Fotokopi Kartu Pelajar 3. Fotokopi Kartu Tanda Penduduk 4. Surat keterangan dari sekolah',
                    'mechanism' => '1. Pendaftaran di sekolah (pengajuan surat keterangan) 2. Verifikasi data siswa 3. Penerbitan surat keterangan 4. Pengantar ke kepolisian',
                    'processing_time' => '2-3 hari kerja',
                    'fee' => 0,
                    'product' => 'Surat keterangan kelakuan baik dari sekolah untuk pengurusan SKCK',
                    'complaint_handling' => 'Layanan pengaduan via email: mtsnmalang2adm@gmail.com atau nomor WA: 0851 8337 5008',
                    'user_types_allowed' => ['siswa'],
                    'is_active' => true,
                    'created_by' => $adminId,
                ],
                [
                    'name' => 'Kegiatan Ekstrakurikuler',
                    'code' => 'EKS-016',
                    'description' => 'Pendaftaran dan informasi kegiatan ekstrakurikuler',
                    'mode' => 'offline',
                    'requirements' => '1. Formulir pendaftaran 2. Minat dan bakat siswa 3. Surat izin dari orang tua (untuk kegiatan eksternal)',
                    'mechanism' => '1. Sosialisasi kegiatan ekskul 2. Pendaftaran di loket 3. Seleksi (jika diperlukan) 4. Pelaksanaan kegiatan',
                    'processing_time' => 'Sesuai jadwal pendaftaran',
                    'fee' => 0,
                    'product' => 'Keanggotaan dalam kegiatan ekstrakurikuler dan jadwal kegiatan',
                    'complaint_handling' => 'Layanan pengaduan via email: mtsnmalang2adm@gmail.com atau nomor WA: 0851 8337 5008',
                    'user_types_allowed' => ['siswa'],
                    'is_active' => true,
                    'created_by' => $adminId,
                ],
                [
                    'name' => 'Permohonan Izin Studi Banding',
                    'code' => 'ISB-017',
                    'description' => 'Permohonan izin untuk kegiatan studi banding',
                    'mode' => 'hybrid',
                    'requirements' => '1. Proposal kegiatan 2. Surat permohonan resmi 3. Rencana pelaksanaan kegiatan 4. Surat tanggung jawab',
                    'mechanism' => '1. Pengajuan surat permohonan 2. Verifikasi proposal 3. Persetujuan oleh kepala sekolah 4. Penjadwalan kegiatan',
                    'processing_time' => '3-5 hari kerja',
                    'fee' => 0,
                    'product' => 'Surat izin pelaksanaan studi banding yang telah disahkan',
                    'complaint_handling' => 'Layanan pengaduan via email: mtsnmalang2adm@gmail.com atau nomor WA: 0851 8337 5008',
                    'user_types_allowed' => ['pegawai'],
                    'is_active' => true,
                    'created_by' => $adminId,
                ],
                [
                    'name' => 'Pelayanan Perpustakaan',
                    'code' => 'PERPUS-018',
                    'description' => 'Layanan peminjaman dan pengembalian buku perpustakaan',
                    'mode' => 'offline',
                    'requirements' => '1. Kartu perpustakaan 2. Identitas siswa/guru 3. Buku yang ingin dipinjam',
                    'mechanism' => '1. Pendaftaran di meja layanan 2. Pengecekan ketersediaan buku 3. Proses peminjaman 4. Pengembalian dan pengecekan',
                    'processing_time' => 'Sesuai jam operasional',
                    'fee' => 0,
                    'product' => 'Buku yang dipinjam sesuai ketentuan peminjaman perpustakaan',
                    'complaint_handling' => 'Layanan pengaduan via email: mtsnmalang2adm@gmail.com atau nomor WA: 0851 8337 5008',
                    'user_types_allowed' => ['siswa', 'pegawai'],
                    'is_active' => true,
                    'created_by' => $adminId,
                ],
                [
                    'name' => 'Permohonan Surat Tugas Guru',
                    'code' => 'STG-019',
                    'description' => 'Permohonan surat tugas untuk guru dalam kegiatan dinas',
                    'mode' => 'online',
                    'requirements' => '1. Formulir permohonan 2. Surat perintah dari instansi 3. Jadwal kegiatan 4. Rencana pelaksanaan tugas',
                    'mechanism' => '1. Pengajuan online melalui sistem 2. Verifikasi oleh atasan 3. Persetujuan kepala sekolah 4. Penerbitan surat tugas',
                    'processing_time' => '1-2 hari kerja',
                    'fee' => 0,
                    'product' => 'Surat tugas yang telah ditandatangani dan distempel resmi',
                    'complaint_handling' => 'Layanan pengaduan via email: mtsnmalang2adm@gmail.com atau nomor WA: 0851 8337 5008',
                    'user_types_allowed' => ['pegawai'],
                    'is_active' => true,
                    'created_by' => $adminId,
                ],
                [
                    'name' => 'Kegiatan Praktik Kerja Lapangan',
                    'code' => 'PKL-020',
                    'description' => 'Pelayanan untuk kegiatan praktik kerja lapangan siswa',
                    'mode' => 'hybrid',
                    'requirements' => '1. Surat pengantar dari sekolah 2. Proposal PKL 3. Identitas tempat PKL 4. Jadwal pelaksanaan',
                    'mechanism' => '1. Pendaftaran PKL 2. Penentuan tempat PKL 3. Pengajuan surat pengantar 4. Monitoring dan evaluasi',
                    'processing_time' => '2-3 hari kerja',
                    'fee' => 0,
                    'product' => 'Surat pengantar PKL dan laporan pelaksanaan PKL',
                    'complaint_handling' => 'Layanan pengaduan via email: mtsnmalang2adm@gmail.com atau nomor WA: 0851 8337 5008',
                    'user_types_allowed' => ['siswa'],
                    'is_active' => true,
                    'created_by' => $adminId,
                ],
            ];

            foreach ($services as $serviceData) {
                // Check if service with this code already exists
                $existingService = Service::where('code', $serviceData['code'])->first();
                if (!$existingService) {
                    Service::create($serviceData);
                }
            }
        });
    }
}
