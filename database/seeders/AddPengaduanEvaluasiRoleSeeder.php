<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AddPengaduanEvaluasiRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create additional permissions for Pengaduan & Evaluasi management
        $permissions = [
            // Pengaduan (Complaints) permissions
            'view complaints',
            'create complaints',
            'edit complaints',
            'delete complaints',
            'process complaints',
            'resolve complaints',
            'export complaints',

            // Whistleblowing permissions
            'view whistleblowing',
            'manage whistleblowing',

            // Survey & Evaluation permissions
            'view surveys',
            'create surveys',
            'edit surveys',
            'delete surveys',
            'manage survey responses',
            'export survey results',

            // SKM (Survei Kepuasan Masyarakat) permissions
            'view skm',
            'manage skm',
            'export skm results',

            // SPAK (Survei Persepsi Anti Korupsi) permissions
            'view spak',
            'manage spak',
            'export spak results',

            // Performance monitoring permissions
            'view performance reports',
            'export performance reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create the new role: Pengaduan & Evaluasi Manager
        $pengaduanEvaluasiRole = Role::firstOrCreate(['name' => 'pengaduan-evaluasi-manager']);

        // Assign permissions to the role
        $pengaduanEvaluasiRole->syncPermissions([
            // Complaint management
            'view complaints',
            'create complaints',
            'edit complaints',
            'process complaints',
            'resolve complaints',
            'export complaints',

            // Whistleblowing
            'view whistleblowing',
            'manage whistleblowing',

            // Survey management
            'view surveys',
            'create surveys',
            'edit surveys',
            'manage survey responses',
            'export survey results',

            // SKM
            'view skm',
            'manage skm',
            'export skm results',

            // SPAK
            'view spak',
            'manage spak',
            'export spak results',

            // Performance reports
            'view performance reports',
            'export performance reports',
        ]);

        $this->command->info('Pengaduan & Evaluasi Manager role created successfully!');
        $this->command->info('Permissions assigned to the role.');
    }
}
