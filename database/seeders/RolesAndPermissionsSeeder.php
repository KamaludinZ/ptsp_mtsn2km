<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for user management
        Permission::firstOrCreate(['name' => 'view users']);
        Permission::firstOrCreate(['name' => 'create users']);
        Permission::firstOrCreate(['name' => 'edit users']);
        Permission::firstOrCreate(['name' => 'delete users']);

        // Create permissions for service management
        Permission::firstOrCreate(['name' => 'view services']);
        Permission::firstOrCreate(['name' => 'create services']);
        Permission::firstOrCreate(['name' => 'edit services']);
        Permission::firstOrCreate(['name' => 'delete services']);

        // Create permissions for ticket management
        Permission::firstOrCreate(['name' => 'view tickets']);
        Permission::firstOrCreate(['name' => 'create tickets']);
        Permission::firstOrCreate(['name' => 'edit tickets']);
        Permission::firstOrCreate(['name' => 'delete tickets']);
        Permission::firstOrCreate(['name' => 'process tickets']);
        Permission::firstOrCreate(['name' => 'approve tickets']);

        // Create permissions for complaint management
        Permission::firstOrCreate(['name' => 'view complaints']);
        Permission::firstOrCreate(['name' => 'create complaints']);
        Permission::firstOrCreate(['name' => 'edit complaints']);
        Permission::firstOrCreate(['name' => 'process complaints']);
        Permission::firstOrCreate(['name' => 'resolve complaints']);

        // Create permissions for survey management
        Permission::firstOrCreate(['name' => 'view surveys']);
        Permission::firstOrCreate(['name' => 'create surveys']);
        Permission::firstOrCreate(['name' => 'edit surveys']);
        Permission::firstOrCreate(['name' => 'manage survey responses']);

        // Create permissions for visitor management
        Permission::firstOrCreate(['name' => 'manage visitors']);

        // Create permissions for front desk operations
        Permission::firstOrCreate(['name' => 'front desk operations']);
        Permission::firstOrCreate(['name' => 'triage visitors']);
        Permission::firstOrCreate(['name' => 'register offline services']);

        // Create permissions for back office operations
        Permission::firstOrCreate(['name' => 'back office operations']);
        Permission::firstOrCreate(['name' => 'workflow management']);
        Permission::firstOrCreate(['name' => 'approval operations']);

        // Create permissions for administration
        Permission::firstOrCreate(['name' => 'admin access']);

        // Create roles and assign permissions
        $adminRole = Role::firstOrCreate(['name' => 'super-admin']);
        $adminRole->givePermissionTo(Permission::all());

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo([
            'view users', 'create users', 'edit users', 
            'view services', 'create services', 'edit services',
            'view tickets', 'edit tickets', 'process tickets',
            'view complaints', 'process complaints', 'resolve complaints',
            'view surveys', 'create surveys', 'edit surveys',
            'manage visitors', 'front desk operations', 'back office operations'
        ]);

        $tuRole = Role::firstOrCreate(['name' => 'tu']); // Tata Usaha
        $tuRole->givePermissionTo([
            'view users',
            'view services',
            'view tickets', 'edit tickets', 'process tickets',
            'view complaints', 'process complaints',
            'manage visitors', 'front desk operations', 'back office operations'
        ]);

        $kepalaTU = Role::firstOrCreate(['name' => 'kepala-tu']); // Kepala Tata Usaha
        $kepalaTU->givePermissionTo([
            'view users',
            'view services',
            'view tickets', 'edit tickets', 'process tickets', 'approve tickets',
            'view complaints', 'process complaints', 'resolve complaints',
            'manage visitors', 'front desk operations', 'back office operations'
        ]);

        $kepalaSekolah = Role::firstOrCreate(['name' => 'kepala-sekolah']); // Kepala Sekolah
        $kepalaSekolah->givePermissionTo([
            'view tickets', 'approve tickets',
            'view complaints', 'resolve complaints',
            'view surveys', 'manage survey responses'
        ]);

        $wakaKesiswaan = Role::firstOrCreate(['name' => 'waka-kesiswaan']); // Wakil Kepala Sekolah Kesiswaan
        $wakaKesiswaan->givePermissionTo([
            'view tickets', 'process tickets', 'approve tickets',
            'view complaints', 'process complaints'
        ]);

        $wakaKurikulum = Role::firstOrCreate(['name' => 'waka-kurikulum']); // Wakil Kepala Sekolah Kurikulum
        $wakaKurikulum->givePermissionTo([
            'view tickets', 'process tickets', 'approve tickets',
            'view complaints', 'process complaints'
        ]);

        $wakaSarpras = Role::firstOrCreate(['name' => 'waka-sarpras']); // Wakil Kepala Sekolah Sarpras
        $wakaSarpras->givePermissionTo([
            'view tickets', 'process tickets', 'approve tickets',
            'view complaints', 'process complaints'
        ]);

        $wakaHumas = Role::firstOrCreate(['name' => 'waka-humas']); // Wakil Kepala Sekolah Humas
        $wakaHumas->givePermissionTo([
            'view tickets', 'process tickets', 'approve tickets',
            'view complaints', 'process complaints'
        ]);

        $guruRole = Role::firstOrCreate(['name' => 'guru']); // Teacher
        $guruRole->givePermissionTo([
            'create tickets', 'view tickets'
        ]);

        $pegawaiRole = Role::firstOrCreate(['name' => 'pegawai']); // Employee
        $pegawaiRole->givePermissionTo([
            'create tickets', 'view tickets'
        ]);

        $siswaRole = Role::firstOrCreate(['name' => 'siswa']); // Student
        $siswaRole->givePermissionTo([
            'create tickets', 'view tickets'
        ]);

        $walimuridRole = Role::firstOrCreate(['name' => 'walimurid']); // Parent
        $walimuridRole->givePermissionTo([
            'create tickets', 'view tickets'
        ]);

        $alumniRole = Role::firstOrCreate(['name' => 'alumni']);
        $alumniRole->givePermissionTo([
            'create tickets', 'view tickets'
        ]);

        $instansiRole = Role::firstOrCreate(['name' => 'instansi']); // Institution
        $instansiRole->givePermissionTo([
            'create tickets', 'view tickets'
        ]);

        $umumRole = Role::firstOrCreate(['name' => 'umum']); // General public
        $umumRole->givePermissionTo([
            'create tickets', 'view tickets'
        ]);

        // Create a super admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@pts-mtsn2-malang.sch.id'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'user_type' => 'pegawai',
            ]
        );
        $superAdmin->assignRole('super-admin');

        // Create a role for managing complaints and evaluations
        $pengelolaPengaduanRole = Role::firstOrCreate(['name' => 'pengelola-pengaduan-evaluasi']);
        $pengelolaPengaduanRole->givePermissionTo([
            'view complaints',
            'create complaints',
            'edit complaints',
            'process complaints',
            'resolve complaints',
            'view surveys',
            'create surveys',
            'edit surveys',
            'manage survey responses',
        ]);
    }
}