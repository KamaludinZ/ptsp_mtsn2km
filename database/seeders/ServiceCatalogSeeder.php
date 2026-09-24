<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\ServiceCategory;

class ServiceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create admin user
        $adminUser = \App\Models\User::first();
        if (!$adminUser) {
            $adminUser = \App\Models\User::create([
                'name' => 'Admin PTSP',
                'email' => 'admin@mtsn2kotamalang.sch.id',
                'password' => bcrypt('password'),
            ]);
        }

        // Create Categories
        $categories = [
            ['name' => 'Surat Keterangan', 'description' => 'Layanan pembuatan berbagai surat keterangan'],
            ['name' => 'Legalisir', 'description' => 'Layanan legalisir dokumen'],
            ['name' => 'Administrasi Siswa', 'description' => 'Layanan administrasi siswa'],
            ['name' => 'Kesiswaan', 'description' => 'Layanan terkait kesiswaan'],
        ];

        foreach ($categories as $cat) {
            ServiceCategory::firstOrCreate(['name' => $cat['name']], $cat);
        }

        // Get category IDs
        $catSuratKeterangan = ServiceCategory::where('name', 'Surat Keterangan')->first();
        $catLegalisir = ServiceCategory::where('name', 'Legalisir')->first();
        $catAdministrasi = ServiceCategory::where('name', 'Administrasi Siswa')->first();
        $catKesiswaan = ServiceCategory::where('name', 'Kesiswaan')->first();

        // Create Services
        $services = [
            [
                'code' => 'SKAS-001',
                'name' => 'Surat Keterangan Siswa Aktif',
                'slug' => 'surat-keterangan-siswa-aktif',
                'description' => 'Layanan pembuatan surat keterangan untuk siswa yang masih aktif belajar di MTsN 2 Kota Malang. Surat ini dapat digunakan untuk keperluan administrasi seperti beasiswa, pindah sekolah, atau keperluan lainnya.',
                'mode' => 'online',
                'is_digital_product' => true,
                'requirements' => "1. Fotocopy Kartu Pelajar\n2. Fotocopy Kartu Keluarga\n3. Surat Pengantar dari Orang Tua/Wali",
                'mechanism' => "1. Pemohon mengisi formulir online\n2. Upload dokumen persyaratan\n3. Verifikasi oleh petugas\n4. Penandatanganan oleh Kepala Madrasah\n5. Dokumen dapat diunduh",
                'processing_time' => '2 hari kerja',
                'fee' => 0,
                'product' => 'Surat Keterangan Siswa Aktif dalam format PDF yang telah ditandatangani dan distempel resmi',
                'complaint_handling' => 'Pengaduan dapat disampaikan melalui email ptsp@mtsn2kotamalang.sch.id atau telepon (0341) 123456',
                'user_types_allowed' => ['siswa', 'walimurid'],
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'code' => 'LEG-002',
                'name' => 'Legalisir Ijazah',
                'slug' => 'legalisir-ijazah',
                'description' => 'Layanan legalisir ijazah untuk alumni MTsN 2 Kota Malang. Legalisir diperlukan untuk keperluan pendaftaran kuliah, melamar pekerjaan, atau keperluan administratif lainnya.',
                'mode' => 'hybrid',
                'is_digital_product' => false,
                'requirements' => "1. Fotocopy Ijazah yang akan dilegalisir\n2. Fotocopy KTP Pemohon\n3. Surat Kuasa (jika dikuasakan)",
                'mechanism' => "1. Pemohon mengajukan permohonan online atau datang langsung\n2. Petugas memverifikasi keaslian dokumen\n3. Legalisir oleh petugas yang berwenang\n4. Pengambilan dokumen di kantor PTSP",
                'processing_time' => '3 hari kerja',
                'fee' => 10000,
                'product' => 'Ijazah yang telah dilegalisir dengan cap dan tanda tangan pejabat berwenang',
                'complaint_handling' => 'Pengaduan dapat disampaikan melalui email ptsp@mtsn2kotamalang.sch.id atau telepon (0341) 123456',
                'user_types_allowed' => ['alumni', 'umum'],
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'code' => 'SKL-003',
                'name' => 'Surat Keterangan Lulus',
                'slug' => 'surat-keterangan-lulus',
                'description' => 'Layanan pembuatan surat keterangan lulus bagi siswa yang telah menyelesaikan pendidikan di MTsN 2 Kota Malang namun ijazah belum terbit. Dapat digunakan sebagai pengganti sementara ijazah.',
                'mode' => 'online',
                'is_digital_product' => true,
                'requirements' => "1. Fotocopy Kartu Ujian\n2. Fotocopy KTP/Kartu Pelajar\n3. Pas Foto 3x4 sebanyak 2 lembar",
                'mechanism' => "1. Pemohon mengisi formulir online\n2. Upload dokumen persyaratan\n3. Verifikasi data kelulusan\n4. Penerbitan surat keterangan\n5. Dokumen dapat diunduh",
                'processing_time' => '1 hari kerja',
                'fee' => 0,
                'product' => 'Surat Keterangan Lulus dalam format PDF yang telah ditandatangani dan distempel',
                'complaint_handling' => 'Pengaduan dapat disampaikan melalui email ptsp@mtsn2kotamalang.sch.id atau telepon (0341) 123456',
                'user_types_allowed' => ['alumni'],
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'code' => 'SREKO-004',
                'name' => 'Surat Rekomendasi',
                'slug' => 'surat-rekomendasi',
                'description' => 'Layanan pembuatan surat rekomendasi dari sekolah untuk keperluan beasiswa, lomba, atau kegiatan lainnya yang memerlukan rekomendasi dari pihak sekolah.',
                'mode' => 'offline',
                'is_digital_product' => false,
                'requirements' => "1. Fotocopy Kartu Pelajar\n2. Surat Permohonan dari Siswa/Orang Tua\n3. Dokumen pendukung (proposal kegiatan/undangan)",
                'mechanism' => "1. Pemohon datang ke kantor PTSP\n2. Mengisi formulir permohonan\n3. Menyerahkan dokumen persyaratan\n4. Verifikasi oleh Wali Kelas\n5. Penandatanganan oleh Kepala Madrasah\n6. Pengambilan dokumen di loket PTSP",
                'processing_time' => '3 hari kerja',
                'fee' => 0,
                'product' => 'Surat Rekomendasi yang ditandatangani Kepala Madrasah dan distempel resmi',
                'complaint_handling' => 'Pengaduan dapat disampaikan melalui email ptsp@mtsn2kotamalang.sch.id atau telepon (0341) 123456',
                'user_types_allowed' => ['siswa', 'walimurid'],
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'code' => 'SKKB-005',
                'name' => 'Surat Keterangan Kelakuan Baik',
                'slug' => 'surat-keterangan-kelakuan-baik',
                'description' => 'Layanan pembuatan surat keterangan kelakuan baik untuk siswa. Surat ini diperlukan untuk keperluan beasiswa, pindah sekolah, atau administrasi lainnya.',
                'mode' => 'online',
                'is_digital_product' => true,
                'requirements' => "1. Fotocopy Kartu Pelajar\n2. Surat Permohonan\n3. Tidak memiliki catatan pelanggaran berat",
                'mechanism' => "1. Pemohon mengisi formulir online\n2. Upload dokumen persyaratan\n3. Pengecekan riwayat pelanggaran siswa\n4. Verifikasi oleh guru BK\n5. Penandatanganan oleh Kepala Madrasah\n6. Dokumen dapat diunduh",
                'processing_time' => '2 hari kerja',
                'fee' => 0,
                'product' => 'Surat Keterangan Kelakuan Baik dalam format PDF',
                'complaint_handling' => 'Pengaduan dapat disampaikan melalui email ptsp@mtsn2kotamalang.sch.id atau telepon (0341) 123456',
                'user_types_allowed' => ['siswa', 'walimurid'],
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'code' => 'DUP-006',
                'name' => 'Duplikat Ijazah',
                'slug' => 'duplikat-ijazah',
                'description' => 'Layanan pembuatan duplikat ijazah bagi alumni yang ijazah aslinya hilang atau rusak. Dilengkapi dengan berita acara kehilangan dari kepolisian.',
                'mode' => 'offline',
                'is_digital_product' => false,
                'requirements' => "1. Surat Keterangan Hilang dari Kepolisian\n2. Fotocopy KTP\n3. Pas Foto 3x4 sebanyak 4 lembar\n4. Fotocopy Ijazah (jika ada)\n5. Surat Pernyataan bermaterai",
                'mechanism' => "1. Pemohon datang ke kantor PTSP\n2. Menyerahkan dokumen persyaratan\n3. Verifikasi data alumni\n4. Proses pembuatan duplikat\n5. Penandatanganan oleh Kepala Madrasah\n6. Pengambilan dokumen sesuai jadwal",
                'processing_time' => '14 hari kerja',
                'fee' => 50000,
                'product' => 'Duplikat Ijazah yang telah ditandatangani dan distempel',
                'complaint_handling' => 'Pengaduan dapat disampaikan melalui email ptsp@mtsn2kotamalang.sch.id atau telepon (0341) 123456',
                'user_types_allowed' => ['alumni'],
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'code' => 'TRN-007',
                'name' => 'Transkrip Nilai',
                'slug' => 'transkrip-nilai',
                'description' => 'Layanan pembuatan transkrip nilai untuk siswa atau alumni MTsN 2 Kota Malang. Berisi daftar nilai selama masa pendidikan.',
                'mode' => 'hybrid',
                'is_digital_product' => true,
                'requirements' => "1. Fotocopy Ijazah/Kartu Pelajar\n2. Fotocopy KTP\n3. Surat Permohonan",
                'mechanism' => "1. Pemohon mengajukan permohonan online atau offline\n2. Verifikasi data nilai\n3. Pencetakan transkrip nilai\n4. Penandatanganan oleh Kepala Madrasah\n5. Pengambilan dokumen atau download",
                'processing_time' => '3 hari kerja',
                'fee' => 15000,
                'product' => 'Transkrip Nilai yang telah ditandatangani dan distempel',
                'complaint_handling' => 'Pengaduan dapat disampaikan melalui email ptsp@mtsn2kotamalang.sch.id atau telepon (0341) 123456',
                'user_types_allowed' => ['siswa', 'alumni'],
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'code' => 'SKPB-008',
                'name' => 'Surat Keterangan Pindah Sekolah',
                'slug' => 'surat-keterangan-pindah-sekolah',
                'description' => 'Layanan pembuatan surat keterangan pindah sekolah bagi siswa yang akan pindah ke sekolah lain.',
                'mode' => 'offline',
                'is_digital_product' => false,
                'requirements' => "1. Surat Permohonan Pindah dari Orang Tua\n2. Fotocopy Kartu Pelajar\n3. Fotocopy Kartu Keluarga\n4. Surat Penerimaan dari Sekolah Tujuan",
                'mechanism' => "1. Orang tua/wali mengajukan permohonan\n2. Konsultasi dengan Wali Kelas dan BK\n3. Verifikasi administrasi siswa\n4. Penerbitan surat keterangan pindah\n5. Pengambilan dokumen dan berkas siswa",
                'processing_time' => '5 hari kerja',
                'fee' => 0,
                'product' => 'Surat Keterangan Pindah Sekolah dan dokumen pendukung lainnya',
                'complaint_handling' => 'Pengaduan dapat disampaikan melalui email ptsp@mtsn2kotamalang.sch.id atau telepon (0341) 123456',
                'user_types_allowed' => ['siswa', 'walimurid'],
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'code' => 'SKPRES-009',
                'name' => 'Surat Keterangan Prestasi',
                'slug' => 'surat-keterangan-prestasi',
                'description' => 'Layanan pembuatan surat keterangan prestasi siswa untuk keperluan beasiswa, lomba, atau penghargaan.',
                'mode' => 'online',
                'is_digital_product' => true,
                'requirements' => "1. Fotocopy Kartu Pelajar\n2. Fotocopy Sertifikat/Piagam Prestasi\n3. Surat Permohonan",
                'mechanism' => "1. Pemohon mengisi formulir online\n2. Upload dokumen persyaratan dan bukti prestasi\n3. Verifikasi oleh bagian kesiswaan\n4. Penerbitan surat keterangan\n5. Dokumen dapat diunduh",
                'processing_time' => '2 hari kerja',
                'fee' => 0,
                'product' => 'Surat Keterangan Prestasi dalam format PDF',
                'complaint_handling' => 'Pengaduan dapat disampaikan melalui email ptsp@mtsn2kotamalang.sch.id atau telepon (0341) 123456',
                'user_types_allowed' => ['siswa', 'walimurid'],
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'code' => 'SKBSW-010',
                'name' => 'Surat Keterangan Beasiswa',
                'slug' => 'surat-keterangan-beasiswa',
                'description' => 'Layanan pembuatan surat keterangan untuk keperluan pengajuan beasiswa. Berisi informasi status siswa dan kondisi ekonomi.',
                'mode' => 'online',
                'is_digital_product' => true,
                'requirements' => "1. Fotocopy Kartu Pelajar\n2. Fotocopy Kartu Keluarga\n3. Surat Keterangan Tidak Mampu (jika ada)\n4. Formulir pengajuan beasiswa",
                'mechanism' => "1. Pemohon mengisi formulir online\n2. Upload dokumen persyaratan\n3. Verifikasi data ekonomi siswa\n4. Penerbitan surat keterangan\n5. Dokumen dapat diunduh",
                'processing_time' => '2 hari kerja',
                'fee' => 0,
                'product' => 'Surat Keterangan untuk keperluan beasiswa dalam format PDF',
                'complaint_handling' => 'Pengaduan dapat disampaikan melalui email ptsp@mtsn2kotamalang.sch.id atau telepon (0341) 123456',
                'user_types_allowed' => ['siswa', 'walimurid'],
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
        ];

        foreach ($services as $serviceData) {
            $service = Service::firstOrCreate(
                ['code' => $serviceData['code']],
                $serviceData
            );

            // Attach categories
            if ($serviceData['code'] == 'SKAS-001' || $serviceData['code'] == 'SKL-003' ||
                $serviceData['code'] == 'SKKB-005' || $serviceData['code'] == 'SKPB-008' ||
                $serviceData['code'] == 'SKPRES-009' || $serviceData['code'] == 'SKBSW-010') {
                $service->categories()->sync([$catSuratKeterangan->id]);
            } elseif ($serviceData['code'] == 'LEG-002') {
                $service->categories()->sync([$catLegalisir->id]);
            } elseif ($serviceData['code'] == 'DUP-006' || $serviceData['code'] == 'TRN-007') {
                $service->categories()->sync([$catAdministrasi->id]);
            } else {
                $service->categories()->sync([$catKesiswaan->id]);
            }
        }
    }
}
