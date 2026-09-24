<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Support\Str;

class SchoolServiceExamplesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Dapatkan atau buat kategori
        $categories = [
            'Akademik' => ServiceCategory::firstOrCreate(['name' => 'Akademik'], ['description' => 'Layanan terkait akademik dan pembelajaran']),
            'Administrasi' => ServiceCategory::firstOrCreate(['name' => 'Administrasi'], ['description' => 'Layanan administrasi dan surat-menyurat']),
            'Kesiswaan' => ServiceCategory::firstOrCreate(['name' => 'Kesiswaan'], ['description' => 'Layanan kepesertadidikan dan ekstrakurikuler']),
            'Kepegawaian' => ServiceCategory::firstOrCreate(['name' => 'Kepegawaian'], ['description' => 'Layanan untuk tenaga pendidik dan kependidikan']),
        ];

        // Data layanan sekolah
        $services = [
            [
                'name' => 'Pendaftaran Peserta Didik Baru (PPDB)',
                'code' => 'AKD-001',
                'description' => 'Layanan pendaftaran untuk calon peserta didik baru tahun ajaran berikutnya melalui sistem online.',
                'mode' => 'online',
                'requirements' => json_encode([
                    'Kartu Keluarga (KK)',
                    'Akta Kelahiran',
                    'Ijazah/Surat Keterangan Lulus SD/MI',
                    'Pas Foto 3x4 (2 lembar)',
                    'Surat Keterangan Sehat dari Dokter',
                ]),
                'mechanism' => 'Pendaftaran dilakukan secara online melalui website sekolah. Setelah mendaftar, calon peserta didik akan mendapatkan nomor pendaftaran. Verifikasi berkas dilakukan secara online, kemudian akan ada pengumuman hasil seleksi.',
                'processing_time' => '7-14 hari kerja',
                'fee' => 0,
                'product' => 'Nomor Pendaftaran dan Kartu Peserta',
                'complaint_handling' => 'Pengaduan dapat disampaikan melalui menu Pengaduan atau menghubungi Panitia PPDB di nomor (0341) 123456',
                'user_types_allowed' => ['umum', 'siswa', 'orang_tua'],
                'is_digital_product' => true,
                'is_active' => true,
                'created_by' => 1,
                'category' => 'Akademik',
            ],
            [
                'name' => 'Pembuatan Surat Keterangan Siswa Aktif',
                'code' => 'ADM-001',
                'description' => 'Layanan pembuatan surat keterangan untuk siswa yang masih aktif bersekolah di MTsN 2 Kota Malang.',
                'mode' => 'hybrid',
                'requirements' => json_encode([
                    'Formulir Permohonan yang telah diisi',
                    'Fotokopi Kartu Pelajar',
                    'Surat Pengantar dari Wali Kelas (untuk keperluan tertentu)',
                ]),
                'mechanism' => 'Pemohon dapat mengajukan permohonan secara online atau datang langsung ke Tata Usaha. Untuk pengajuan online, isi formulir dan upload dokumen. Untuk offline, serahkan berkas ke petugas TU. Surat akan diproses dan dapat diambil sesuai waktu yang ditentukan.',
                'processing_time' => '1-3 hari kerja',
                'fee' => 0,
                'product' => 'Surat Keterangan Siswa Aktif bermaterai',
                'complaint_handling' => 'Hubungi Kepala TU melalui menu Pengaduan atau langsung ke ruang Tata Usaha',
                'user_types_allowed' => ['siswa', 'orang_tua'],
                'is_digital_product' => false,
                'is_active' => true,
                'created_by' => 1,
                'category' => 'Administrasi',
            ],
            [
                'name' => 'Legalisir Ijazah dan Transkrip Nilai',
                'code' => 'ADM-002',
                'description' => 'Layanan legalisir dokumen ijazah dan transkrip nilai alumni MTsN 2 Kota Malang untuk keperluan administrasi.',
                'mode' => 'offline',
                'requirements' => json_encode([
                    'Ijazah Asli dan Fotokopi',
                    'Transkrip Nilai Asli dan Fotokopi',
                    'Kartu Identitas (KTP/SIM)',
                    'Surat Kuasa (jika diwakilkan)',
                ]),
                'mechanism' => 'Pemohon datang langsung ke Tata Usaha dengan membawa dokumen asli dan fotokopi. Petugas akan memverifikasi dan melakukan legalisir. Dokumen dapat diambil pada hari yang sama atau keesokan harinya.',
                'processing_time' => '1-2 hari kerja',
                'fee' => 5000,
                'product' => 'Dokumen Ijazah dan Transkrip yang telah dilegalisir',
                'complaint_handling' => 'Sampaikan keluhan melalui menu Pengaduan atau hubungi Kepala TU',
                'user_types_allowed' => ['alumni', 'umum'],
                'is_digital_product' => false,
                'is_active' => true,
                'created_by' => 1,
                'category' => 'Administrasi',
            ],
            [
                'name' => 'Pengajuan Surat Izin Tidak Masuk Sekolah',
                'code' => 'KSW-001',
                'description' => 'Layanan pengajuan surat izin untuk siswa yang berhalangan hadir karena sakit atau keperluan mendadak.',
                'mode' => 'online',
                'requirements' => json_encode([
                    'Surat Keterangan Sakit dari Dokter (jika sakit)',
                    'Surat Keterangan dari Orang Tua/Wali',
                    'Alasan yang jelas',
                ]),
                'mechanism' => 'Orang tua/wali dapat mengajukan izin melalui sistem online dengan mengisi formulir dan mengunggah dokumen pendukung. Sistem akan mengirimkan notifikasi ke wali kelas untuk persetujuan. Setelah disetujui, status akan diupdate di sistem.',
                'processing_time' => '1 hari kerja',
                'fee' => 0,
                'product' => 'Konfirmasi Izin yang telah disetujui',
                'complaint_handling' => 'Hubungi Wakil Kepala Bidang Kesiswaan melalui menu Pengaduan',
                'user_types_allowed' => ['siswa', 'orang_tua'],
                'is_digital_product' => true,
                'is_active' => true,
                'created_by' => 1,
                'category' => 'Kesiswaan',
            ],
            [
                'name' => 'Pendaftaran Ekstrakurikuler',
                'code' => 'KSW-002',
                'description' => 'Layanan pendaftaran untuk mengikuti kegiatan ekstrakurikuler yang tersedia di sekolah.',
                'mode' => 'online',
                'requirements' => json_encode([
                    'Surat Persetujuan Orang Tua',
                    'Fotokopi Kartu Pelajar',
                    'Pas Foto 3x4 (1 lembar)',
                    'Surat Keterangan Sehat (untuk ekstrakurikuler olahraga)',
                ]),
                'mechanism' => 'Siswa mendaftar melalui sistem online dengan memilih ekstrakurikuler yang diminati. Upload dokumen persyaratan. Pembina ekstrakurikuler akan melakukan seleksi jika ada kuota terbatas. Pengumuman hasil seleksi akan dikirim melalui email dan dapat dicek di sistem.',
                'processing_time' => '3-5 hari kerja',
                'fee' => 0,
                'product' => 'Kartu Anggota Ekstrakurikuler',
                'complaint_handling' => 'Hubungi Koordinator Ekstrakurikuler atau sampaikan melalui menu Pengaduan',
                'user_types_allowed' => ['siswa'],
                'is_digital_product' => true,
                'is_active' => true,
                'created_by' => 1,
                'category' => 'Kesiswaan',
            ],
            [
                'name' => 'Pengajuan Cuti Pegawai',
                'code' => 'KEP-001',
                'description' => 'Layanan pengajuan cuti untuk guru dan tenaga kependidikan sesuai dengan ketentuan yang berlaku.',
                'mode' => 'hybrid',
                'requirements' => json_encode([
                    'Formulir Pengajuan Cuti',
                    'Surat Keterangan Pendukung (jika diperlukan)',
                    'Persetujuan Atasan Langsung',
                ]),
                'mechanism' => 'Pegawai dapat mengajukan cuti melalui sistem online atau menyerahkan formulir langsung ke bagian kepegawaian. Permohonan akan diproses melalui hierarki persetujuan dari atasan langsung hingga kepala sekolah. Status pengajuan dapat dipantau melalui sistem.',
                'processing_time' => '2-5 hari kerja',
                'fee' => 0,
                'product' => 'Surat Izin Cuti yang telah disetujui',
                'complaint_handling' => 'Hubungi Kepala Sub Bagian Tata Usaha melalui menu Pengaduan',
                'user_types_allowed' => ['pegawai', 'guru'],
                'is_digital_product' => false,
                'is_active' => true,
                'created_by' => 1,
                'category' => 'Kepegawaian',
            ],
            [
                'name' => 'Pembuatan Surat Tugas',
                'code' => 'KEP-002',
                'description' => 'Layanan pembuatan surat tugas untuk kegiatan dinas guru dan tenaga kependidikan.',
                'mode' => 'offline',
                'requirements' => json_encode([
                    'Surat Undangan/Proposal Kegiatan',
                    'Disposisi Kepala Sekolah',
                    'Data Pegawai yang ditugaskan',
                ]),
                'mechanism' => 'Pegawai atau koordinator kegiatan mengajukan permohonan surat tugas dengan melampirkan surat undangan atau proposal. Setelah mendapat disposisi kepala sekolah, bagian kepegawaian akan memproses surat tugas. Surat dapat diambil setelah ditandatangani oleh kepala sekolah.',
                'processing_time' => '1-2 hari kerja',
                'fee' => 0,
                'product' => 'Surat Tugas yang telah ditandatangani',
                'complaint_handling' => 'Hubungi bagian Kepegawaian atau sampaikan melalui menu Pengaduan',
                'user_types_allowed' => ['pegawai', 'guru'],
                'is_digital_product' => false,
                'is_active' => true,
                'created_by' => 1,
                'category' => 'Kepegawaian',
            ],
            [
                'name' => 'Permohonan Surat Rekomendasi Beasiswa',
                'code' => 'KSW-003',
                'description' => 'Layanan pembuatan surat rekomendasi dari sekolah untuk pengajuan beasiswa siswa berprestasi atau kurang mampu.',
                'mode' => 'hybrid',
                'requirements' => json_encode([
                    'Formulir Permohonan',
                    'Fotokopi Rapor semester terakhir',
                    'Sertifikat Prestasi (jika ada)',
                    'Surat Keterangan Tidak Mampu dari Kelurahan (untuk beasiswa ekonomi)',
                    'Proposal atau form beasiswa yang dituju',
                ]),
                'mechanism' => 'Siswa mengajukan permohonan melalui sistem online atau langsung ke wali kelas. Wali kelas akan memverifikasi data dan meneruskan ke Wakasek Kesiswaan. Setelah disetujui, surat rekomendasi akan dicetak dan ditandatangani oleh Kepala Sekolah.',
                'processing_time' => '3-5 hari kerja',
                'fee' => 0,
                'product' => 'Surat Rekomendasi Beasiswa bermaterai',
                'complaint_handling' => 'Hubungi Wakil Kepala Bidang Kesiswaan melalui menu Pengaduan',
                'user_types_allowed' => ['siswa', 'orang_tua'],
                'is_digital_product' => false,
                'is_active' => true,
                'created_by' => 1,
                'category' => 'Kesiswaan',
            ],
        ];

        // Simpan layanan dan hubungkan dengan kategori
        foreach ($services as $serviceData) {
            $categoryName = $serviceData['category'];
            unset($serviceData['category']);

            $serviceData['slug'] = Str::slug($serviceData['name']) . '-' . time() . '-' . rand(100, 999);

            $service = Service::create($serviceData);

            // Attach kategori
            if (isset($categories[$categoryName])) {
                $service->categories()->attach($categories[$categoryName]->id);
            }

            // Delay sedikit untuk memastikan slug unik
            usleep(10000);
        }
    }
}
