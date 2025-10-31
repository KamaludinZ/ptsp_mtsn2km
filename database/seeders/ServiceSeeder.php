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
        DB::transaction(function () {
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
                    'user_types_allowed' => json_encode(['siswa', 'alumni']),
                    'is_active' => true,
                    'created_by' => 1, // Super Admin user ID
                ],
                [
                    'name' => 'Legalisir Ijazah dan Transkrip Nilai',
                    'code' => 'LIT-002',
                    'description' => 'Legalisir dokumen ijazah dan transkrip nilai asli',
                    'user_types_allowed' => json_encode(['siswa', 'alumni', 'umum']),
                    'is_active' => true,
                    'created_by' => 1,
                ],
                [
                    'name' => 'Surat Rekomendasi Beasiswa',
                    'code' => 'SRB-003',
                    'description' => 'Surat rekomendasi untuk pengajuan beasiswa',
                    'user_types_allowed' => json_encode(['siswa', 'alumni']),
                    'is_active' => true,
                    'created_by' => 1,
                ],
                [
                    'name' => 'Surat Keterangan Berkelakuan Baik',
                    'code' => 'SKBB-004',
                    'description' => 'Surat keterangan tentang perilaku siswa selama di sekolah',
                    'user_types_allowed' => json_encode(['siswa', 'alumni']),
                    'is_active' => true,
                    'created_by' => 1,
                ],
                [
                    'name' => 'Informasi Akademik Anak',
                    'code' => 'IAA-005',
                    'description' => 'Layanan informasi perkembangan akademik siswa',
                    'user_types_allowed' => json_encode(['walimurid']),
                    'is_active' => true,
                    'created_by' => 1,
                ],
                [
                    'name' => 'Izin Tidak Masuk Sekolah',
                    'code' => 'ITMS-006',
                    'description' => 'Surat izin untuk siswa yang tidak dapat mengikuti kegiatan belajar',
                    'user_types_allowed' => json_encode(['walimurid']),
                    'is_active' => true,
                    'created_by' => 1,
                ],
                [
                    'name' => 'Konsultasi Bimbingan Konseling',
                    'code' => 'KBK-007',
                    'description' => 'Layanan konsultasi dengan guru BK untuk berbagai permasalahan',
                    'user_types_allowed' => json_encode(['walimurid']),
                    'is_active' => true,
                    'created_by' => 1,
                ],
                [
                    'name' => 'Surat Permohonan Kerjasama',
                    'code' => 'SPK-008',
                    'description' => 'Surat untuk mengajukan kerjasama antar instansi',
                    'user_types_allowed' => json_encode(['instansi']),
                    'is_active' => true,
                    'created_by' => 1,
                ],
                [
                    'name' => 'Izin Kegiatan atau Penelitian',
                    'code' => 'IKP-009',
                    'description' => 'Izin untuk melakukan kegiatan atau penelitian di lingkungan sekolah',
                    'user_types_allowed' => json_encode(['instansi']),
                    'is_active' => true,
                    'created_by' => 1,
                ],
                [
                    'name' => 'Permohonan Data Statistik',
                    'code' => 'PDS-010',
                    'description' => 'Permohonan data statistik sekolah untuk keperluan penelitian',
                    'user_types_allowed' => json_encode(['instansi']),
                    'is_active' => true,
                    'created_by' => 1,
                ],
            ];

            foreach ($services as $serviceData) {
                Service::create($serviceData);
            }
        });
    }
}