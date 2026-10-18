<?php

use App\Support\Applicants;
use App\Support\RoleAccess;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Aturan pemohon resmi: pemohon ditandai lewat perannya, jadi ketujuh peran
 * pemohon (siswa, walimurid, alumni, umum, instansi, guru, pegawai) harus ada
 * beserta izin portalnya, juga di basis data yang tidak menjalankan seeder
 * lagi. Aman dijalankan ulang; izin yang diberikan admin tetap.
 */
return new class extends Migration
{
    public function up(): void
    {
        RoleAccess::sync();

        foreach (Applicants::ROLES as $role) {
            Role::findOrCreate($role, 'web')->givePermissionTo(RolePermissionSeeder::permissionsFor($role));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Peran pemohon bisa sudah dipegang akun: tidak dihapus.
    }
};
