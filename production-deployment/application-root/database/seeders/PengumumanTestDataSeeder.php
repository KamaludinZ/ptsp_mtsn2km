<?php

namespace Database\Seeders;

use App\Models\Pengumuman;
use Illuminate\Database\Seeder;

class PengumumanTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test announcement with both URL and attachment
        Pengumuman::create([
            'title' => 'Contoh Pengumuman dengan Lampiran dan URL',
            'content' => 'Ini adalah contoh pengumuman yang memiliki lampiran PDF dan URL eksternal. Anda dapat melihat tombol dan badge yang sesuai.',
            'category' => 'kegiatan',
            'publish_date' => now(),
            'end_date' => now()->addDays(30),
            'is_active' => true,
            'author' => 'Admin Testing',
            'url' => 'https://www.example.com',
            'attachment' => 'pengumuman_attachments/test_attachment.pdf',
            'view_count' => 0
        ]);

        // Create a test announcement with only URL
        Pengumuman::create([
            'title' => 'Contoh Pengumuman dengan URL Eksternal',
            'content' => 'Ini adalah contoh pengumuman yang memiliki URL eksternal untuk informasi lebih lanjut.',
            'category' => 'administrasi',
            'publish_date' => now(),
            'end_date' => now()->addDays(20),
            'is_active' => true,
            'author' => 'Admin Testing',
            'url' => 'https://www.google.com',
            'attachment' => null,
            'view_count' => 0
        ]);

        // Create a test announcement with only PDF attachment
        Pengumuman::create([
            'title' => 'Contoh Pengumuman dengan Lampiran PDF',
            'content' => 'Ini adalah contoh pengumuman yang memiliki lampiran PDF yang dapat diunduh.',
            'category' => 'akademik',
            'publish_date' => now(),
            'end_date' => now()->addDays(15),
            'is_active' => true,
            'author' => 'Admin Testing',
            'url' => null,
            'attachment' => 'pengumuman_attachments/guide_document.pdf',
            'view_count' => 0
        ]);
    }
}
