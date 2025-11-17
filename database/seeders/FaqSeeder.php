<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Faq; // Import the Faq model

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Faq::create([
            'question' => 'Bagaimana cara mengajukan layanan secara online?',
            'answer' => 'Anda dapat mengajukan layanan secara online dengan mengunjungi halaman "Lihat Semua Layanan", memilih layanan yang dibutuhkan, mengisi formulir permohonan, dan mengunggah dokumen persyaratan. Pastikan Anda menyertakan email dan nomor WhatsApp aktif untuk notifikasi.',
            'is_active' => true,
        ]);

        Faq::create([
            'question' => 'Apakah saya akan mendapatkan nomor tiket setelah mengajukan permohonan?',
            'answer' => 'Ya, setelah permohonan Anda diajukan, Anda akan menerima nomor tiket melalui email atau WhatsApp. Nomor tiket ini dapat digunakan untuk melacak status permohonan Anda.',
            'is_active' => true,
        ]);

        Faq::create([
            'question' => 'Bagaimana cara melacak status permohonan layanan saya?',
            'answer' => 'Anda dapat melacak status permohonan Anda dengan menggunakan fitur "Lacak Status Tiket" di halaman utama. Masukkan nomor tiket yang Anda terima untuk melihat perkembangan permohonan Anda secara real-time.',
            'is_active' => true,
        ]);

        Faq::create([
            'question' => 'Bagaimana jika saya ingin mengajukan layanan secara offline?',
            'answer' => 'Anda dapat mengunjungi loket PTSP kami pada jam operasional. Ambil nomor antrean, sampaikan kebutuhan layanan Anda kepada petugas, serahkan dokumen persyaratan, dan berikan email serta nomor WhatsApp aktif Anda. Anda akan menerima nomor tiket dan diberitahu estimasi waktu penyelesaian.',
            'is_active' => true,
        ]);

        Faq::create([
            'question' => 'Kapan jam operasional loket PTSP?',
            'answer' => 'Loket PTSP kami beroperasi setiap hari Senin hingga Jumat, mulai pukul 08:00 hingga 15:00 WIB.',
            'is_active' => true,
        ]);

        Faq::create([
            'question' => 'Bagaimana cara menghubungi PTSP jika ada pertanyaan lebih lanjut?',
            'answer' => 'Anda dapat menghubungi kami melalui halaman "Kontak" yang tersedia di website ini, atau datang langsung ke loket PTSP pada jam operasional.',
            'is_active' => true,
        ]);
    }
}
