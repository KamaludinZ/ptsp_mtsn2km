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
        Permission::create(['name' => 'view services']);
        Permission::create(['name' => 'create services']);
        Permission::create(['name' => 'edit services']);
        Permission::create(['name' => 'delete services']);

        // Create permissions for ticket management
        Permission::create(['name' => 'view tickets']);
        Permission::create(['name' => 'create tickets']);
        Permission::create(['name' => 'edit tickets']);
        Permission::create(['name' => 'delete tickets']);
        Permission::create(['name' => 'process tickets']);
        Permission::create(['name' => 'approve tickets']);

        // Create permissions for complaint management
        Permission::create(['name' => 'view complaints']);
        Permission::create(['name' => 'create complaints']);
        Permission::create(['name' => 'edit complaints']);
        Permission::create(['name' => 'process complaints']);
        Permission::create(['name' => 'resolve complaints']);

        // Create permissions for survey management
        Permission::create(['name' => 'view surveys']);
        Permission::create(['name' => 'create surveys']);
        Permission::create(['name' => 'edit surveys']);
        Permission::create(['name' => 'manage survey responses']);

        // Create permissions for visitor management
        Permission::create(['name' => 'manage visitors']);

        // Create permissions for front desk operations
        Permission::create(['name' => 'front desk operations']);
        Permission::create(['name' => 'triage visitors']);
        Permission::create(['name' => 'register offline services']);

        // Create permissions for back office operations
        Permission::create(['name' => 'back office operations']);
        Permission::create(['name' => 'workflow management']);
        Permission::create(['name' => 'approval operations']);

        // Create permissions for administration
        Permission::create(['name' => 'admin access']);

        // Create roles and assign permissions
        $adminRole = Role::create(['name' => 'super-admin']);
        $adminRole->givePermissionTo(Permission::all());

        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo([
            'view users', 'create users', 'edit users', 
            'view services', 'create services', 'edit services',
            'view tickets', 'edit tickets', 'process tickets',
            'view complaints', 'process complaints', 'resolve complaints',
            'view surveys', 'create surveys', 'edit surveys',
            'manage visitors', 'front desk operations', 'back office operations'
        ]);

        $tuRole = Role::create(['name' => 'tu']); // Tata Usaha
        $tuRole->givePermissionTo([
            'view users',
            'view services',
            'view tickets', 'edit tickets', 'process tickets',
            'view complaints', 'process complaints',
            'manage visitors', 'front desk operations', 'back office operations'
        ]);

        $kepalaTU = Role::create(['name' => 'kepala-tu']); // Kepala Tata Usaha
        $kepalaTU->givePermissionTo([
            'view users',
            'view services',
            'view tickets', 'edit tickets', 'process tickets', 'approve tickets',
            'view complaints', 'process complaints', 'resolve complaints',
            'manage visitors', 'front desk operations', 'back office operations'
        ]);

        $kepalaSekolah = Role::create(['name' => 'kepala-sekolah']); // Kepala Sekolah
        $kepalaSekolah->givePermissionTo([
            'view tickets', 'approve tickets',
            'view complaints', 'resolve complaints',
            'view surveys', 'manage survey responses'
        ]);

        $wakaKesiswaan = Role::create(['name' => 'waka-kesiswaan']); // Wakil Kepala Sekolah Kesiswaan
        $wakaKesiswaan->givePermissionTo([
            'view tickets', 'process tickets', 'approve tickets',
            'view complaints', 'process complaints'
        ]);

        $wakaKurikulum = Role::create(['name' => 'waka-kurikulum']); // Wakil Kepala Sekolah Kurikulum
        $wakaKurikulum->givePermissionTo([
            'view tickets', 'process tickets', 'approve tickets',
            'view complaints', 'process complaints'
        ]);

        $wakaSarpras = Role::create(['name' => 'waka-sarpras']); // Wakil Kepala Sekolah Sarpras
        $wakaSarpras->givePermissionTo([
            'view tickets', 'process tickets', 'approve tickets',
            'view complaints', 'process complaints'
        ]);

        $wakaHumas = Role::create(['name' => 'waka-humas']); // Wakil Kepala Sekolah Humas
        $wakaHumas->givePermissionTo([
            'view tickets', 'process tickets', 'approve tickets',
            'view complaints', 'process complaints'
        ]);

        $guruRole = Role::create(['name' => 'guru']); // Teacher
        $guruRole->givePermissionTo([
            'create tickets', 'view tickets'
        ]);

        $pegawaiRole = Role::create(['name' => 'pegawai']); // Employee
        $pegawaiRole->givePermissionTo([
            'create tickets', 'view tickets'
        ]);

        $siswaRole = Role::create(['name' => 'siswa']); // Student
        $siswaRole->givePermissionTo([
            'create tickets', 'view tickets'
        ]);

        $walimuridRole = Role::create(['name' => 'walimurid']); // Parent
        $walimuridRole->givePermissionTo([
            'create tickets', 'view tickets'
        ]);

        $alumniRole = Role::create(['name' => 'alumni']);
        $alumniRole->givePermissionTo([
            'create tickets', 'view tickets'
        ]);

        $instansiRole = Role::create(['name' => 'instansi']); // Institution
        $instansiRole->givePermissionTo([
            'create tickets', 'view tickets'
        ]);

        $umumRole = Role::create(['name' => 'umum']); // General public
        $umumRole->givePermissionTo([
            'create tickets', 'view tickets'
        ]);

        // Create a super admin user
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@pts-mtsn2-malang.sch.id',
            'password' => bcrypt('password'),
            'user_type' => 'pegawai',
        ]);
        $superAdmin->assignRole('super-admin');
    }
}