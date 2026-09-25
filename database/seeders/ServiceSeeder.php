<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceCategory;
use App\Models\Service;
use App\Models\ServiceRequirement;
use App\Models\ServiceComponent;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Services are owned by the first admin account (IDs differ per database)
        $adminId = \App\Models\User::role('admin')->orderBy('id')->value('id');

        DB::transaction(function () use ($adminId) {
            // Create service categories
            $categories = [
                [
                    'name' => 'Layanan Akademik',
                    'description' => 'Layanan terkait kegiatan akademik siswa dan alumni',
                ],
                [
                    'name' => 'Layanan Wali Murid',
                    'description' => 'Layanan untuk orang tua/wali murid',
                ],
                [
                    'name' => 'Layanan Instansi',
                    'description' => 'Layanan untuk institusi dan mitra kerja',
                ],
                [
                    'name' => 'Layanan Umum',
                    'description' => 'Layanan untuk masyarakat umum',
                ],
            ];

            $createdCategories = [];
            foreach ($categories as $category) {
                $createdCategories[$category['name']] = ServiceCategory::create($category);
            }

            // Skip creating components for now - will be created per service
            // Service components are linked to services in the migration

            // Create services using available columns from migration
            $services = [
                [
                    'name' => 'Surat Keterangan Siswa Aktif',
                    'code' => 'SKSA-001',
                    'description' => 'Surat keterangan yang menyatakan bahwa siswa masih aktif belajar di MTsN 2 Kota Malang',
                    'mode' => 'online',
                    'requirements' => '1. Formulir permohonan 2. Fotokopi Kartu Pelajar 3. Surat permohonan dari orang tua/wali',
                    'mechanism' => '1. Pengajuan online melalui portal 2. Verifikasi data 3. Proses administrasi 4. Penerbitan surat',
                    'processing_time' => '1-2 hari kerja',
                    'fee' => 0,
                    'product' => 'Surat Keterangan Siswa Aktif yang telah ditandatangani secara digital',
                    'complaint_handling' => 'Layanan pengaduan via email: mtsnmalang2adm@gmail.com atau nomor WA: 0851 8337 5008',
                    'user_types_allowed' => ['siswa', 'alumni'],
                    'is_active' => true,
                    'created_by' => $adminId,
                ],
                [
                    'name' => 'Legalisir Ijazah dan Transkrip Nilai',
                    'code' => 'LIT-002',
                    'description' => 'Legalisir dokumen ijazah dan transkrip nilai asli',
                    'mode' => 'hybrid',
                    'requirements' => '1. Fotokopi ijazah/transkrip nilai 2. Fotokopi Kartu Tanda Penduduk 3. Dokumen asli untuk verifikasi',
                    'mechanism' => '1. Pendaftaran di loket pelayanan atau online 2. Verifikasi dokumen asli 3. Proses legalisasi 4. Penyerahan dokumen',
                    'processing_time' => '1-3 hari kerja',
                    'fee' => 10000,
                    'product' => 'Ijazah dan transkrip nilai yang telah dilegalisir dengan tanda tangan dan cap basah',
                    'complaint_handling' => 'Layanan pengaduan via email: mtsnmalang2adm@gmail.com atau nomor WA: 0851 8337 5008',
                    'user_types_allowed' => ['siswa', 'alumni', 'umum'],
                    'is_active' => true,
                    'created_by' => $adminId,
                ],
                [
                    'name' => 'Surat Rekomendasi Beasiswa',
                    'code' => 'SRB-003',
                    'description' => 'Surat rekomendasi untuk pengajuan beasiswa',
                    'mode' => 'online',
                    'requirements' => '1. Formulir permohonan 2. Riwayat prestasi akademik/non-akademik 3. Surat permohonan dari orang tua/wali',
                    'mechanism' => '1. Pengajuan online 2. Verifikasi data dan prestasi 3. Penulisan rekomendasi 4. TTD dan pengesahan',
                    'processing_time' => '2-3 hari kerja',
                    'fee' => 0,
                    'product' => 'Surat rekomendasi beasiswa yang telah ditandatangani dan distempel',
                    'complaint_handling' => 'Layanan pengaduan via email: mtsnmalang2adm@gmail.com atau nomor WA: 0851 8337 5008',
                    'user_types_allowed' => ['siswa', 'alumni'],
                    'is_active' => true,
                    'created_by' => $adminId,
                ],
                [
                    'name' => 'Surat Keterangan Berkelakuan Baik',
                    'code' => 'SKBB-004',
                    'description' => 'Surat keterangan tentang perilaku siswa selama di sekolah',
                    'mode' => 'online',
                    'requirements' => '1. Formulir permohonan 2. Surat permohonan dari orang tua/wali 3. Tidak sedang dalam masalah disiplin',
                    'mechanism' => '1. Pengajuan online 2. Verifikasi data perilaku siswa 3. Verifikasi dari wali kelas 4. Penerbitan surat',
                    'processing_time' => '1-2 hari kerja',
                    'fee' => 0,
                    'product' => 'Surat keterangan berkelakuan baik yang telah ditandatangani dan distempel',
                    'complaint_handling' => 'Layanan pengaduan via email: mtsnmalang2adm@gmail.com atau nomor WA: 0851 8337 5008',
                    'user_types_allowed' => ['siswa', 'alumni'],
                    'is_active' => true,
                    'created_by' => $adminId,
                ],
                [
                    'name' => 'Informasi Akademik Anak',
                    'code' => 'IAA-005',
                    'description' => 'Layanan informasi perkembangan akademik siswa',
                    'mode' => 'online',
                    'requirements' => '1. Identitas orang tua/wali 2. Identitas siswa 3. Keperluan informasi yang spesifik',
                    'mechanism' => '1. Pengajuan online 2. Verifikasi hubungan keluarga 3. Pengumpulan data akademik 4. Penyampaian informasi',
                    'processing_time' => '1-2 hari kerja',
                    'fee' => 0,
                    'product' => 'Laporan perkembangan akademik siswa yang terkini',
                    'complaint_handling' => 'Layanan pengaduan via email: mtsnmalang2adm@gmail.com atau nomor WA: 0851 8337 5008',
                    'user_types_allowed' => ['walimurid'],
                    'is_active' => true,
                    'created_by' => $adminId,
                ],
                [
                    'name' => 'Izin Tidak Masuk Sekolah',
                    'code' => 'ITMS-006',
                    'description' => 'Surat izin untuk siswa yang tidak dapat mengikuti kegiatan belajar',
                    'mode' => 'online',
                    'requirements' => '1. Surat dari orang tua/wali 2. Alasan ketidakhadiran 3. Durasi izin',
                    'mechanism' => '1. Pengajuan online 2. Verifikasi dari wali kelas 3. Penerbitan surat izin 4. Update data absensi',
                    'processing_time' => '1 hari kerja',
                    'fee' => 0,
                    'product' => 'Surat izin tidak masuk sekolah yang telah disetujui',
                    'complaint_handling' => 'Layanan pengaduan via email: mtsnmalang2adm@gmail.com atau nomor WA: 0851 8337 5008',
                    'user_types_allowed' => ['walimurid'],
                    'is_active' => true,
                    'created_by' => $adminId,
                ],
                [
                    'name' => 'Konsultasi Bimbingan Konseling',
                    'code' => 'KBK-007',
                    'description' => 'Layanan konsultasi dengan guru BK untuk berbagai permasalahan',
                    'mode' => 'offline',
                    'requirements' => '1. Janji temu (opsional) 2. Identitas siswa/orang tua 3. Permasalahan yang ingin didiskusikan',
                    'mechanism' => '1. Pendaftaran di ruang BK 2. Penjadwalan sesi konseling 3. Pelaksanaan konseling 4. Dokumentasi hasil',
                    'processing_time' => 'Sesuai jadwal',
                    'fee' => 0,
                    'product' => 'Hasil konseling dan rekomendasi penanganan permasalahan',
                    'complaint_handling' => 'Layanan pengaduan via email: mtsnmalang2adm@gmail.com atau nomor WA: 0851 8337 5008',
                    'user_types_allowed' => ['walimurid'],
                    'is_active' => true,
                    'created_by' => $adminId,
                ],
                [
                    'name' => 'Surat Permohonan Kerjasama',
                    'code' => 'SPK-008',
                    'description' => 'Surat untuk mengajukan kerjasama antar instansi',
                    'mode' => 'hybrid',
                    'requirements' => '1. Proposal kerjasama 2. Identitas instansi 3. Tujuan dan manfaat kerjasama',
                    'mechanism' => '1. Pengajuan surat permohonan 2. Verifikasi proposal 3. Negosiasi MOU 4. Penandatanganan kerjasama',
                    'processing_time' => '3-7 hari kerja',
                    'fee' => 0,
                    'product' => 'Surat balasan permohonan kerjasama atau MOU yang telah ditandatangani',
                    'complaint_handling' => 'Layanan pengaduan via email: mtsnmalang2adm@gmail.com atau nomor WA: 0851 8337 5008',
                    'user_types_allowed' => ['instansi'],
                    'is_active' => true,
                    'created_by' => $adminId,
                ],
                [
                    'name' => 'Izin Kegiatan atau Penelitian',
                    'code' => 'IKP-009',
                    'description' => 'Izin untuk melakukan kegiatan atau penelitian di lingkungan sekolah',
                    'mode' => 'offline',
                    'requirements' => '1. Surat permohonan resmi 2. Proposal kegiatan/penelitian 3. Identitas pelaku kegiatan',
                    'mechanism' => '1. Pendaftaran di bagian tata usaha 2. Review proposal 3. Verifikasi lapangan 4. Penerbitan izin',
                    'processing_time' => '2-5 hari kerja',
                    'fee' => 0,
                    'product' => 'Surat izin kegiatan/penelitian yang telah ditandatangani dan distempel',
                    'complaint_handling' => 'Layanan pengaduan via email: mtsnmalang2adm@gmail.com atau nomor WA: 0851 8337 5008',
                    'user_types_allowed' => ['instansi'],
                    'is_active' => true,
                    'created_by' => $adminId,
                ],
                [
                    'name' => 'Permohonan Data Statistik',
                    'code' => 'PDS-010',
                    'description' => 'Permohonan data statistik sekolah untuk keperluan penelitian',
                    'mode' => 'offline',
                    'requirements' => '1. Surat permohonan resmi 2. Proposal penelitian 3. Identitas peneliti 4. Tujuan penggunaan data',
                    'mechanism' => '1. Pengajuan surat permohonan 2. Verifikasi tujuan 3. Pengumpulan data 4. Penyerahan data',
                    'processing_time' => '3-5 hari kerja',
                    'fee' => 0,
                    'product' => 'Data statistik sekolah dalam format digital/tercetak sesuai permintaan',
                    'complaint_handling' => 'Layanan pengaduan via email: mtsnmalang2adm@gmail.com atau nomor WA: 0851 8337 5008',
                    'user_types_allowed' => ['instansi'],
                    'is_active' => true,
                    'created_by' => $adminId,
                ],
            ];

            foreach ($services as $serviceData) {
                // Check if service with this code already exists
                $existingService = Service::where('code', $serviceData['code'])->first();
                if ($existingService) {
                    // Update existing service
                    $existingService->update($serviceData);
                } else {
                    // Create new service
                    Service::create($serviceData);
                }
            }
        });
    }
}