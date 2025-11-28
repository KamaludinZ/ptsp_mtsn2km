<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RegistrationCode;
use Carbon\Carbon;

class RegistrationCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample registration codes for testing

        // Kode untuk Siswa
        RegistrationCode::create([
            'code' => '1234567890',
            'user_type' => 'siswa',
            'description' => 'Kode registrasi untuk siswa baru tahun ajaran 2025/2026',
            'is_active' => true,
            'is_single_use' => false,
            'max_uses' => null, // unlimited
            'used_count' => 0,
            'expires_at' => Carbon::now()->addMonths(6),
        ]);

        // Kode untuk Guru (single use)
        RegistrationCode::create([
            'code' => '0987654321',
            'user_type' => 'guru',
            'description' => 'Kode registrasi untuk guru baru',
            'is_active' => true,
            'is_single_use' => true,
            'max_uses' => 1,
            'used_count' => 0,
            'expires_at' => Carbon::now()->addYear(),
        ]);

        // Kode untuk Pegawai (limited uses)
        RegistrationCode::create([
            'code' => '1111111111',
            'user_type' => 'pegawai',
            'description' => 'Kode registrasi untuk pegawai administrasi',
            'is_active' => true,
            'is_single_use' => false,
            'max_uses' => 10,
            'used_count' => 0,
            'expires_at' => Carbon::now()->addMonths(3),
        ]);

        // Kode tambahan untuk Siswa
        RegistrationCode::create([
            'code' => '2222222222',
            'user_type' => 'siswa',
            'description' => 'Kode registrasi siswa pindahan',
            'is_active' => true,
            'is_single_use' => false,
            'max_uses' => 50,
            'used_count' => 0,
            'expires_at' => Carbon::now()->addMonths(12),
        ]);

        // Kode untuk Guru
        RegistrationCode::create([
            'code' => '3333333333',
            'user_type' => 'guru',
            'description' => 'Kode registrasi guru honorer',
            'is_active' => true,
            'is_single_use' => false,
            'max_uses' => 5,
            'used_count' => 0,
            'expires_at' => null, // no expiration
        ]);
    }
}
