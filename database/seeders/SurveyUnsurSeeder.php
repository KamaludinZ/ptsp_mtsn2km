<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SurveyUnsur;

class SurveyUnsurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SurveyUnsur::truncate();

        $skmUnsurs = [
            ['name' => 'Persyaratan', 'code' => 'skm-1', 'survey_type' => 'skm'],
            ['name' => 'Prosedur', 'code' => 'skm-2', 'survey_type' => 'skm'],
            ['name' => 'Waktu Pelayanan', 'code' => 'skm-3', 'survey_type' => 'skm'],
            ['name' => 'Produk Spesifikasi Jenis Pelayanan', 'code' => 'skm-4', 'survey_type' => 'skm'],
            ['name' => 'Kompetensi Pelaksana', 'code' => 'skm-5', 'survey_type' => 'skm'],
            ['name' => 'Perilaku Pelaksana', 'code' => 'skm-6', 'survey_type' => 'skm'],
            ['name' => 'Maklumat Pelayanan', 'code' => 'skm-7', 'survey_type' => 'skm'],
            ['name' => 'Penanganan Pengaduan, Saran, dan Masukan', 'code' => 'skm-8', 'survey_type' => 'skm'],
            ['name' => 'Biaya/Tarif', 'code' => 'skm-9', 'survey_type' => 'skm'],
        ];

        $spakUnsurs = [
            ['name' => 'Manipulasi peraturan', 'code' => 'spak-1', 'survey_type' => 'spak'],
            ['name' => 'Penyalahgunaan jabatan', 'code' => 'spak-2', 'survey_type' => 'spak'],
            ['name' => 'Menjual pengaruh', 'code' => 'spak-3', 'survey_type' => 'spak'],
            ['name' => 'Transparansi biaya', 'code' => 'spak-4', 'survey_type' => 'spak'],
            ['name' => 'Biaya tambahan', 'code' => 'spak-5', 'survey_type' => 'spak'],
            ['name' => 'Pemberian hadiah', 'code' => 'spak-6', 'survey_type' => 'spak'],
            ['name' => 'Transparansi transaksi', 'code' => 'spak-7', 'survey_type' => 'spak'],
            ['name' => 'Praktik percaloan', 'code' => 'spak-8', 'survey_type' => 'spak'],
            ['name' => 'Kecurangan pelayanan', 'code' => 'spak-9', 'survey_type' => 'spak'],
            ['name' => 'Transaksi rahasia', 'code' => 'spak-10', 'survey_type' => 'spak'],
        ];

        foreach ($skmUnsurs as $unsur) {
            SurveyUnsur::create($unsur);
        }

        foreach ($spakUnsurs as $unsur) {
            SurveyUnsur::create($unsur);
        }
    }
}
