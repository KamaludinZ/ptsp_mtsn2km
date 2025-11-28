<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SurveyQuestion;

class SurveyQuestionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        SurveyQuestion::truncate();

        // IDENTITAS RESPONDEN
        $identityQuestions = [
            [
                'survey_type' => 'identity',
                'question' => 'Pilih Jenis Pelayanan',
                'options' => json_encode([
                    'Mutasi Siswa Masuk',
                    'Mutasi Siswa Keluar',
                    'Penerbitan Surat Rekomendasi Siswa',
                    'Penerimaan Peserta Didik Baru',
                    'Izin Melaksanakan Penelitian/Observasi',
                    'Selesai Melaksanakan Penelitian/Observasi',
                    'Surat Keterangan Kerusakan Ijazah',
                    'Surat Keterangan Pengganti Ijazah Hilang',
                    'Legalisasi Ijazah Offline',
                    'Pengambilan Ijazah',
                    'Perbaikan Kesalahan Penulisan Ijazah',
                    'Penerimaan Tamu Studi Banding',
                    'Penerimaan Tamu Dinas',
                    'Pengaduan Masyarakat',
                    'SOP Kerjasama dengan Wartawan',
                    'Screening Kesehatan Siswa',
                    'Penerimaan Iuran Komite'
                ]),
                'field_type' => 'select',
                'order' => 1,
            ],
            [
                'survey_type' => 'identity',
                'question' => 'Nama Lengkap',
                'options' => null,
                'field_type' => 'text',
                'order' => 2,
            ],
            [
                'survey_type' => 'identity',
                'question' => 'Usia',
                'options' => json_encode([
                    'Dibawah 20 Tahun',
                    '21 s.d 30 Tahun',
                    '31 s.d 40 Tahun',
                    '41 s.d 50 Tahun',
                    'Diatas 50 Tahun'
                ]),
                'field_type' => 'radio',
                'order' => 3,
            ],
            [
                'survey_type' => 'identity',
                'question' => 'Jenis Kelamin',
                'options' => json_encode(['Laki-laki', 'Perempuan']),
                'field_type' => 'radio',
                'order' => 4,
            ],
            [
                'survey_type' => 'identity',
                'question' => 'Pendidikan',
                'options' => json_encode(['SD', 'SMP', 'SMA', 'D3', 'D4/S1', 'S2', 'S3']),
                'field_type' => 'select',
                'order' => 5,
            ],
            [
                'survey_type' => 'identity',
                'question' => 'Pekerjaan',
                'options' => json_encode([
                    'PNS/TNI/POLRI',
                    'Pegawai Swasta',
                    'Wiraswasta',
                    'Petani/Pekebun',
                    'Pelajar/Mahasiswa',
                    'Lainnya'
                ]),
                'field_type' => 'select',
                'order' => 6,
            ],
            [
                'survey_type' => 'identity',
                'question' => 'No. Telepon',
                'options' => null,
                'field_type' => 'tel',
                'order' => 7,
            ],
            [
                'survey_type' => 'identity',
                'question' => 'Kode Tiket Layanan',
                'options' => null,
                'field_type' => 'text',
                'order' => 8,
            ],
            [
                'survey_type' => 'identity',
                'question' => 'Email Aktif',
                'options' => null,
                'field_type' => 'email',
                'order' => 9,
            ],
        ];

        // SKM - 9 UNSUR
        $skmQuestions = [
            [
                'question' => 'Bagaimana pendapat Saudara tentang kesesuaian persyaratan layanan di MTsN 2 Kota Malang dengan jenis pelayanannya?',
                'options' => json_encode(['Tidak Sesuai', 'Kurang Sesuai', 'Sesuai', 'Sangat Sesuai']),
                'unsur' => 'Persyaratan',
            ],
            [
                'question' => 'Bagaimana pemahaman Saudara tentang kemudahan prosedur pelayanan di MTsN 2 Kota Malang?',
                'options' => json_encode(['Tidak Mudah', 'Kurang Mudah', 'Mudah', 'Sangat Mudah']),
                'unsur' => 'Prosedur',
            ],
            [
                'question' => 'Bagaimana pendapat Saudara tentang kecepatan pelayanan di MTsN 2 Kota Malang?',
                'options' => json_encode(['Tidak Cepat', 'Kurang Cepat', 'Cepat', 'Sangat Cepat']),
                'unsur' => 'Waktu Pelayanan',
            ],
            [
                'question' => 'Bagaimana pendapat Saudara tentang Jenis pelayanan ini di MTsN 2 Kota Malang?',
                'options' => json_encode(['Tidak Bagus', 'Kurang Bagus', 'Bagus', 'Sangat Bagus']),
                'unsur' => 'Produk Spesifikasi Jenis Pelayanan',
            ],
            [
                'question' => 'Bagaimana pendapat Saudara tentang kemampuan petugas di MTsN 2 Kota Malang dalam memberikan pelayanan?',
                'options' => json_encode(['Tidak Mampu', 'Kurang Mampu', 'Mampu', 'Sangat Mampu']),
                'unsur' => 'Kompetensi Pelaksana',
            ],
            [
                'question' => 'Bagaimana pendapat Saudara tentang kesopanan dan keramahan petugas di MTsN 2 Kota Malang dalam memberikan pelayanan?',
                'options' => json_encode(['Tidak Sopan', 'Kurang Sopan', 'Sopan', 'Sangat Sopan']),
                'unsur' => 'Perilaku Pelaksana',
            ],
            [
                'question' => 'Bagaimana pendapat Saudara tentang maklumat pelayanan di MTsN 2 Kota Malang?',
                'options' => json_encode(['Tidak Jelas', 'Kurang Jelas', 'Jelas', 'Sangat Jelas']),
                'unsur' => 'Maklumat Pelayanan',
            ],
            [
                'question' => 'Bagaimana pendapat Saudara tentang Sarana dan Penanganan atas Pengaduan, Kritik dan Saran pelayanan di MTsN 2 Kota Malang?',
                'options' => json_encode(['Tidak Bagus', 'Kurang Bagus', 'Bagus', 'Sangat Bagus']),
                'unsur' => 'Penanganan Pengaduan, Saran, dan Masukan',
            ],
            [
                'question' => 'Bagaimana pendapat Saudara tentang kesesuaian antara biaya pelayanan dengan yang ada pada standar pelayanan di MTsN 2 Kota Malang (semua jenis layanan gratis)?',
                'options' => json_encode(['Selalu Tidak Sesuai', 'Terkadang Sesuai', 'Sesuai', 'Selalu Sesuai']),
                'unsur' => 'Biaya/Tarif',
            ],
        ];

        // SPAK - 10 PERTANYAAN
        $spakQuestions = [
            [
                'question' => 'Apakah Saudara pernah mengalami atau mengetahui adanya manipulasi peraturan di MTsN 2 Kota Malang?',
                'options' => json_encode(['Sangat sering', 'Sering', 'Jarang', 'Tidak Pernah']),
                'unsur' => 'Manipulasi peraturan',
            ],
            [
                'question' => 'Apakah Saudara pernah mengalami atau mengetahui adanya petugas yang menyalahgunaan jabatan di MTsN 2 Kota Malang?',
                'options' => json_encode(['Sangat sering', 'Sering', 'Jarang', 'Tidak Pernah']),
                'unsur' => 'Penyalahgunaan jabatan',
            ],
            [
                'question' => 'Apakah Saudara pernah mengalami atau mengetahui adanya petugas yang menjual pengaruh di MTsN 2 Kota Malang?',
                'options' => json_encode(['Sangat sering', 'Sering', 'Jarang', 'Tidak Pernah']),
                'unsur' => 'Menjual pengaruh',
            ],
            [
                'question' => 'Bagaimana menurut Saudara dengan transparansi biaya yang ada di MTsN 2 Kota Malang?',
                'options' => json_encode(['Tidak Transparan', 'Kurang Transparan', 'Transparan', 'Sangat Transparan']),
                'unsur' => 'Transparansi biaya',
            ],
            [
                'question' => 'Apakah Saudara pernah mengalami atau mengetahui adanya petugas yang meminta biaya tambahan diluar ketentuan dan standar pelayanan di MTsN 2 Kota Malang?',
                'options' => json_encode(['Sangat sering', 'Sering', 'Jarang', 'Tidak Pernah']),
                'unsur' => 'Biaya tambahan',
            ],
            [
                'question' => 'Apakah Saudara pernah mengetahui adanya pemberian hadiah kepada petugas di MTsN 2 Kota Malang?',
                'options' => json_encode(['Sangat sering', 'Sering', 'Jarang', 'Tidak Pernah']),
                'unsur' => 'Pemberian hadiah',
            ],
            [
                'question' => 'Bagaimana menurut Saudara dengan transparansi transaksi pembayaran yang ada di MTsN 2 Kota Malang?',
                'options' => json_encode(['Tidak Transparan', 'Kurang Transparan', 'Transparan', 'Sangat Transparan']),
                'unsur' => 'Transparansi transaksi',
            ],
            [
                'question' => 'Apakah Saudara pernah mengalami atau mengetahui adanya petugas yang melakukan praktik percaloan di MTsN 2 Kota Malang?',
                'options' => json_encode(['Sangat sering', 'Sering', 'Jarang', 'Tidak Pernah']),
                'unsur' => 'Praktik percaloan',
            ],
            [
                'question' => 'Apakah Saudara pernah mengalami atau mengetahui adanya petugas yang melakukan kecurangan dalam pelayanan di MTsN 2 Kota Malang?',
                'options' => json_encode(['Sangat sering', 'Sering', 'Jarang', 'Tidak Pernah']),
                'unsur' => 'Kecurangan pelayanan',
            ],
            [
                'question' => 'Apakah Saudara pernah mengalami atau mengetahui adanya petugas yang melakukan transaksi rahasia dalam melayani di MTsN 2 Kota Malang?',
                'options' => json_encode(['Sangat sering', 'Sering', 'Jarang', 'Tidak Pernah']),
                'unsur' => 'Transaksi rahasia',
            ],
        ];

        // Insert Identity Questions
        foreach ($identityQuestions as $q) {
            $q['type'] = $q['survey_type'];
            SurveyQuestion::create($q);
        }

        // Insert SKM Questions
        foreach ($skmQuestions as $index => $q) {
            SurveyQuestion::create([
                'type' => 'skm',
                'survey_type' => 'skm',
                'question' => $q['question'],
                'options' => $q['options'],
                'field_type' => 'radio',
                'order' => $index + 1,
                'is_required' => true,
                'is_active' => true,
                'unsur_id' => \App\Models\SurveyUnsur::where('name', $q['unsur'])->first()->id ?? null,
            ]);
        }

        // Insert SPAK Questions
        foreach ($spakQuestions as $index => $q) {
            SurveyQuestion::create([
                'type' => 'spak',
                'survey_type' => 'spak',
                'question' => $q['question'],
                'options' => $q['options'],
                'field_type' => 'radio',
                'order' => $index + 1,
                'is_required' => true,
                'is_active' => true,
                'unsur_id' => \App\Models\SurveyUnsur::where('name', $q['unsur'])->first()->id ?? null,
            ]);
        }

        $this->command->info('Survey questions seeded successfully!');
    }
}
