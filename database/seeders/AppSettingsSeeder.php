<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppSetting;

class AppSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Branding Settings
            [
                'key' => 'app_logo',
                'value' => 'images/logo.png',
                'type' => 'image',
                'category' => 'branding',
                'display_name' => 'Logo Aplikasi',
                'description' => 'Logo utama aplikasi yang ditampilkan di header (Rekomendasi: 200x200px, PNG/JPG)',
            ],
            [
                'key' => 'app_favicon',
                'value' => 'favicon.ico',
                'type' => 'image',
                'category' => 'branding',
                'display_name' => 'Favicon',
                'description' => 'Favicon aplikasi yang ditampilkan di browser tab (Rekomendasi: 32x32px atau 64x64px, ICO/PNG)',
            ],
            [
                'key' => 'app_name',
                'value' => 'PTSP MTsN 2 KOTA MALANG',
                'type' => 'text',
                'category' => 'branding',
                'display_name' => 'Nama Aplikasi',
                'description' => 'Nama aplikasi yang ditampilkan di seluruh sistem',
            ],
            [
                'key' => 'app_name_full',
                'value' => 'Pelayanan Terpadu Satu Pintu - MTsN 2 Kota Malang',
                'type' => 'text',
                'category' => 'branding',
                'display_name' => 'Nama Lengkap Aplikasi',
                'description' => 'Nama lengkap aplikasi untuk keperluan formal',
            ],
            [
                'key' => 'app_tagline',
                'value' => 'Pelayanan Terpadu Satu Pintu',
                'type' => 'text',
                'category' => 'branding',
                'display_name' => 'Tagline Aplikasi',
                'description' => 'Tagline atau slogan aplikasi',
            ],
            [
                'key' => 'footer_description',
                'value' => 'Pelayanan Terpadu Satu Pintu sesuai Permen PANRB 15/2014 untuk kemudahan akses layanan masyarakat.',
                'type' => 'textarea',
                'category' => 'branding',
                'display_name' => 'Deskripsi Footer',
                'description' => 'Deskripsi singkat yang ditampilkan di footer',
            ],

            // Contact Information - Header
            [
                'key' => 'contact_address',
                'value' => 'Jl. Raya Cemorokandang 77 Kota Malang, Jawa Timur',
                'type' => 'text',
                'category' => 'contact',
                'display_name' => 'Alamat',
                'description' => 'Alamat lengkap institusi',
            ],
            [
                'key' => 'contact_phone',
                'value' => '(0341) 711500',
                'type' => 'text',
                'category' => 'contact',
                'display_name' => 'Nomor Telepon',
                'description' => 'Nomor telepon utama',
            ],
            [
                'key' => 'contact_email',
                'value' => 'mtsnmalang2adm@gmail.com',
                'type' => 'email',
                'category' => 'contact',
                'display_name' => 'Email',
                'description' => 'Alamat email utama',
            ],
            [
                'key' => 'contact_website',
                'value' => 'www.mtsn2kotamalang.sch.id',
                'type' => 'text',
                'category' => 'contact',
                'display_name' => 'Website',
                'description' => 'Alamat website utama',
            ],
            [
                'key' => 'contact_whatsapp_ptsp',
                'value' => '6285183367500',
                'type' => 'text',
                'category' => 'contact',
                'display_name' => 'WhatsApp PTSP',
                'description' => 'Nomor WhatsApp untuk layanan PTSP (format: 62xxx tanpa +)',
            ],
            [
                'key' => 'contact_whatsapp_pengaduan',
                'value' => '6285183375008',
                'type' => 'text',
                'category' => 'contact',
                'display_name' => 'WhatsApp Pengaduan',
                'description' => 'Nomor WhatsApp untuk pengaduan (format: 62xxx tanpa +)',
            ],

            // Social Media
            [
                'key' => 'social_facebook',
                'value' => '#',
                'type' => 'url',
                'category' => 'social',
                'display_name' => 'Facebook',
                'description' => 'URL profil Facebook',
            ],
            [
                'key' => 'social_twitter',
                'value' => '#',
                'type' => 'url',
                'category' => 'social',
                'display_name' => 'Twitter/X',
                'description' => 'URL profil Twitter/X',
            ],
            [
                'key' => 'social_instagram',
                'value' => '#',
                'type' => 'url',
                'category' => 'social',
                'display_name' => 'Instagram',
                'description' => 'URL profil Instagram',
            ],
            [
                'key' => 'social_youtube',
                'value' => '#',
                'type' => 'url',
                'category' => 'social',
                'display_name' => 'YouTube',
                'description' => 'URL channel YouTube',
            ],
            [
                'key' => 'social_linkedin',
                'value' => '#',
                'type' => 'url',
                'category' => 'social',
                'display_name' => 'LinkedIn',
                'description' => 'URL profil LinkedIn',
            ],

            // Operating Hours
            [
                'key' => 'operating_hours_weekday',
                'value' => '07.00 - 15.00 WIB',
                'type' => 'text',
                'category' => 'operating_hours',
                'display_name' => 'Jam Operasional Senin-Kamis',
                'description' => 'Jam operasional hari kerja (Senin-Kamis)',
            ],
            [
                'key' => 'operating_hours_friday',
                'value' => '07.00 - 11.00 WIB',
                'type' => 'text',
                'category' => 'operating_hours',
                'display_name' => 'Jam Operasional Jumat',
                'description' => 'Jam operasional hari Jumat',
            ],
            [
                'key' => 'operating_hours_weekend',
                'value' => 'Tutup',
                'type' => 'text',
                'category' => 'operating_hours',
                'display_name' => 'Jam Operasional Sabtu-Minggu',
                'description' => 'Jam operasional hari Sabtu-Minggu',
            ],

            // Related Links
            [
                'key' => 'link_kemenag',
                'value' => 'https://kemenag.go.id',
                'type' => 'url',
                'category' => 'related_links',
                'display_name' => 'Kementerian Agama RI',
                'description' => 'Link ke website Kementerian Agama RI',
            ],
            [
                'key' => 'link_kanwil',
                'value' => 'https://kanwil.kemenag.go.id/jatim',
                'type' => 'url',
                'category' => 'related_links',
                'display_name' => 'Kanwil Kemenag Jatim',
                'description' => 'Link ke website Kanwil Kemenag Jawa Timur',
            ],
            [
                'key' => 'link_kankemenag',
                'value' => 'https://kankemenag.malangkota.go.id',
                'type' => 'url',
                'category' => 'related_links',
                'display_name' => 'Kemenag Kota Malang',
                'description' => 'Link ke website Kemenag Kota Malang',
            ],
            [
                'key' => 'link_sp4n_lapor',
                'value' => 'https://lapor.go.id',
                'type' => 'url',
                'category' => 'related_links',
                'display_name' => 'SP4N Lapor',
                'description' => 'Link ke website SP4N Lapor',
            ],
            [
                'key' => 'link_sippn',
                'value' => 'https://sippn.menpan.go.id',
                'type' => 'url',
                'category' => 'related_links',
                'display_name' => 'SIPPN Menpan',
                'description' => 'Link ke website SIPPN Menpan',
            ],

            // Copyright
            [
                'key' => 'copyright_text',
                'value' => 'PTSP MTsN 2 Kota Malang. Hak Cipta Dilindungi.',
                'type' => 'text',
                'category' => 'general',
                'display_name' => 'Teks Copyright',
                'description' => 'Teks copyright yang ditampilkan di footer',
            ],
            [
                'key' => 'copyright_year',
                'value' => date('Y'),
                'type' => 'text',
                'category' => 'general',
                'display_name' => 'Tahun Copyright',
                'description' => 'Tahun copyright (otomatis dari tahun saat ini)',
            ],
            [
                'key' => 'developed_by',
                'value' => 'Tim PUSKOM',
                'type' => 'text',
                'category' => 'general',
                'display_name' => 'Dikembangkan Oleh',
                'description' => 'Nama tim/organisasi yang mengembangkan aplikasi',
            ],

            // Theme Settings
            [
                'key' => 'theme_public',
                'value' => 'light',
                'type' => 'select',
                'category' => 'theme',
                'display_name' => 'Tema Tampilan Publik',
                'description' => 'Tema DaisyUI untuk tampilan publik (landing page, portal, dll)',
            ],
            [
                'key' => 'theme_admin',
                'value' => 'corporate',
                'type' => 'select',
                'category' => 'theme',
                'display_name' => 'Tema Dashboard Admin',
                'description' => 'Tema DaisyUI untuk dashboard admin',
            ],

            // Version
            [
                'key' => 'app_version',
                'value' => 'v1.0.0',
                'type' => 'text',
                'category' => 'general',
                'display_name' => 'Versi Aplikasi',
                'description' => 'Versi aplikasi saat ini',
            ],

            // General Settings
            [
                'key' => 'enable_maintenance',
                'value' => 'false',
                'type' => 'boolean',
                'category' => 'general',
                'display_name' => 'Mode Maintenance',
                'description' => 'Aktifkan mode maintenance untuk non-admin',
            ],
            [
                'key' => 'maintenance_message',
                'value' => 'Situs sedang dalam maintenance. Silakan kembali beberapa saat lagi.',
                'type' => 'textarea',
                'category' => 'general',
                'display_name' => 'Pesan Maintenance',
                'description' => 'Pesan yang ditampilkan saat mode maintenance aktif',
            ],
        ];

        foreach ($settings as $setting) {
            AppSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('App settings seeded successfully!');
    }
}