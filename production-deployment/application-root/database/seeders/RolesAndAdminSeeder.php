<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Delete old roles that need to be removed
        $rolesToDelete = ['super_admin', 'bendahara'];
        foreach ($rolesToDelete as $roleToDelete) {
            $role = Role::where('name', $roleToDelete)->first();
            if ($role) {
                // Remove role from users first
                $role->users()->detach();
                $role->delete();
                $this->command->info("Role '{$roleToDelete}' deleted.");
            }
        }

        // Create Roles
        $roles = [
            'admin' => 'Admin',
            'kepala_sekolah' => 'Kepala Sekolah',
            'kepala_tu' => 'Kepala TU',
            'petugas_tu' => 'Petugas TU',
            'petugas_loket' => 'Petugas Loket',
            'pemohon' => 'Pemohon',
        ];

        foreach ($roles as $roleName => $roleLabel) {
            Role::firstOrCreate(
                ['name' => $roleName],
                ['guard_name' => 'web']
            );
            $this->command->info("Role '{$roleLabel}' created or already exists.");
        }

        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@mtsn2kotamalang.sch.id'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'user_type' => 'pegawai',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Remove old super_admin role if exists and assign admin role
        if ($admin->hasRole('super_admin')) {
            $admin->removeRole('super_admin');
        }

        if (!$admin->hasAnyRole(['admin', 'super_admin'])) {
            $admin->assignRole('admin');
            $this->command->info('Admin user created and role assigned.');
            $this->command->warn('Email: admin@mtsn2kotamalang.sch.id');
            $this->command->warn('Password: password');
        } else {
            $this->command->info('Admin user already exists.');
        }
    }
}
