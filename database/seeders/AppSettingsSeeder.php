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
                'key' => 'name_full',
                'value' => 'PTSP MTsN 2 KOTA MALANG',
                'type' => 'text',
                'category' => 'branding',
                'display_name' => 'Nama Lengkap Aplikasi',
                'description' => 'Nama lengkap aplikasi yang akan ditampilkan di seluruh halaman',
            ],
            [
                'key' => 'logo',
                'value' => null,
                'type' => 'image',
                'category' => 'branding',
                'display_name' => 'Logo Aplikasi',
                'description' => 'Logo utama aplikasi (PNG/JPG, max 2MB)',
            ],
            [
                'key' => 'description',
                'value' => 'Pelayanan Terpadu Satu Pintu MTsN 2 Kota Malang - Layanan cepat, transparan, dan akuntabel sesuai Permen PANRB 15/2014',
                'type' => 'textarea',
                'category' => 'seo',
                'display_name' => 'Deskripsi Aplikasi',
                'description' => 'Deskripsi untuk SEO meta tags dan halaman about',
            ],
            [
                'key' => 'logo',
                'value' => null,
                'type' => 'image',
                'category' => 'branding',
                'display_name' => 'Logo Aplikasi',
                'description' => 'Logo utama aplikasi (PNG/JPG, max 2MB)',
            ],
            [
                'key' => 'favicon',
                'value' => null,
                'type' => 'image',
                'category' => 'branding',
                'display_name' => 'Favicon',
                'description' => 'Ikon website (ICO, 32x32px)',
            ],

            // Contact Settings
            [
                'key' => 'contact_phone',
                'value' => '(0341) 123456',
                'type' => 'text',
                'category' => 'contact',
                'display_name' => 'Nomor Telepon',
                'description' => 'Nomor telepon yang dapat dihubungi',
            ],
            [
                'key' => 'contact_email',
                'value' => 'info@mtsn2malang.sch.id',
                'type' => 'email',
                'category' => 'contact',
                'display_name' => 'Email',
                'description' => 'Email kontak utama',
            ],
            [
                'key' => 'contact_address',
                'value' => 'Jl. Raya Tumpang No. 123, Kota Malang, Jawa Timur',
                'type' => 'textarea',
                'category' => 'contact',
                'display_name' => 'Alamat Lengkap',
                'description' => 'Alamat lengkap instansi',
            ],
            [
                'key' => 'contact_website',
                'value' => 'www.mtsn2malang.sch.id',
                'type' => 'url',
                'category' => 'contact',
                'display_name' => 'Website',
                'description' => 'URL website resmi',
            ],

            // Social Media Settings
            [
                'key' => 'social_facebook',
                'value' => null,
                'type' => 'url',
                'category' => 'social',
                'display_name' => 'Facebook',
                'description' => 'URL halaman Facebook',
            ],
            [
                'key' => 'social_twitter',
                'value' => null,
                'type' => 'url',
                'category' => 'social',
                'display_name' => 'Twitter/X',
                'description' => 'URL profil Twitter/X',
            ],
            [
                'key' => 'social_instagram',
                'value' => null,
                'type' => 'url',
                'category' => 'social',
                'display_name' => 'Instagram',
                'description' => 'URL profil Instagram',
            ],
            [
                'key' => 'social_youtube',
                'value' => null,
                'type' => 'url',
                'category' => 'social',
                'display_name' => 'YouTube',
                'description' => 'URL channel YouTube',
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
    }
}