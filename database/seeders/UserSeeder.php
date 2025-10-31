<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $roles = [
            'super_admin' => 'Super Administrator',
            'admin' => 'Administrator',
            'front_desk' => 'Petugas Front Desk',
            'back_office' => 'Petugas Back Office',
            'supervisor' => 'Pengawas',
            'kepala_sekolah' => 'Kepala Sekolah',
            'kepala_tu' => 'Kepala TU',
            'guru' => 'Guru',
            'pegawai' => 'Pegawai',
            'siswa' => 'Siswa',
            'walimurid' => 'Wali Murid',
            'alumni' => 'Alumni',
            'instansi' => 'Instansi',
            'umum' => 'Masyarakat Umum',
        ];

        foreach ($roles as $name => $description) {
            Role::firstOrCreate(['name' => $name], [
                'guard_name' => 'web',
            ]);
        }

        // Create permissions
        $permissions = [
            // Dashboard permissions
            'dashboard.view' => 'Melihat Dashboard',
            'dashboard.admin' => 'Dashboard Admin',

            // Front desk permissions
            'frontdesk.access' => 'Akses Front Desk',
            'frontdesk.triage' => 'Melakukan Triage',
            'frontdesk.visitor.manage' => 'Kelola Tamu',
            'frontdesk.service.create' => 'Buat Layanan Walk-in',

            // Back office permissions
            'backoffice.access' => 'Akses Back Office',
            'backoffice.tickets.view' => 'Lihat Tiket',
            'backoffice.tickets.assign' => 'Tugaskan Tiket',
            'backoffice.tickets.process' => 'Proses Tiket',
            'backoffice.tickets.complete' => 'Selesaikan Tiket',
            'backoffice.reports.view' => 'Lihat Laporan',

            // Supervision permissions
            'supervision.access' => 'Akses Supervisi',
            'supervision.complaints.view' => 'Lihat Pengaduan',
            'supervision.complaints.process' => 'Proses Pengaduan',
            'supervision.surveys.view' => 'Lihat Survei',
            'supervision.surveys.manage' => 'Kelola Survei',
            'supervision.performance.view' => 'Lihat Kinerja',

            // Online portal permissions
            'onlineportal.access' => 'Akses Portal Online',
            'onlineportal.services.view' => 'Lihat Layanan',
            'onlineportal.services.apply' => 'Ajukan Layanan',
            'onlineportal.tickets.view' => 'Lihat Tiket Saya',
            'onlineportal.tickets.track' => 'Lacak Tiket',

            // User management
            'users.view' => 'Lihat User',
            'users.create' => 'Buat User',
            'users.edit' => 'Edit User',
            'users.delete' => 'Hapus User',

            // Service management
            'services.view' => 'Lihat Layanan',
            'services.create' => 'Buat Layanan',
            'services.edit' => 'Edit Layanan',
            'services.delete' => 'Hapus Layanan',

            // Settings
            'settings.view' => 'Lihat Pengaturan',
            'settings.edit' => 'Edit Pengaturan',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(['name' => $name], [
                'guard_name' => 'web',
            ]);
        }

        // Create Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin PTSP',
            'email' => 'admin@ptsp.mtsn2malang.sch.id',
            'password' => Hash::make('admin123'),
            'user_type' => 'pegawai',
            'registration_code' => 'SA001',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $superAdminRole = Role::where('name', 'super_admin')->first();
        $superAdmin->assignRole($superAdminRole);
        $superAdmin->givePermissionTo(Permission::all());

        // Create Admin Users
        $adminUsers = [
            [
                'name' => 'Admin PTSP',
                'email' => 'ptsp@mtsn2malang.sch.id',
                'password' => Hash::make('admin123'),
                'user_type' => 'pegawai',
                'registration_code' => 'ADM001',
            ],
        ];

        foreach ($adminUsers as $userData) {
            $user = User::create(array_merge($userData, [
                'is_active' => true,
                'email_verified_at' => now(),
            ]));

            $role = Role::where('name', 'admin')->first();
            $user->assignRole($role);
            $user->givePermissionTo([
                'dashboard.view',
                'supervision.access',
                'supervision.complaints.view',
                'supervision.performance.view',
                'onlineportal.access',
            ]);
        }

        // Create Leadership
        $leadershipUsers = [
            [
                'name' => 'Kepala Sekolah',
                'email' => 'kepsek@mtsn2malang.sch.id',
                'password' => Hash::make('kepsek123'),
                'user_type' => 'pegawai',
                'registration_code' => 'KPS001',
            ],
            [
                'name' => 'Kepala TU',
                'email' => 'katu@mtsn2malang.sch.id',
                'password' => Hash::make('katu123'),
                'user_type' => 'pegawai',
                'registration_code' => 'KTU001',
            ],
        ];

        foreach ($leadershipUsers as $userData) {
            $user = User::create(array_merge($userData, [
                'is_active' => true,
                'email_verified_at' => now(),
            ]));

            $roleName = $userData['user_type'] === 'pegawai' &&
                      (strpos($userData['email'], 'kepsek') !== false ? 'kepala_sekolah' :
                       (strpos($userData['email'], 'katu') !== false ? 'kepala_tu' : 'pegawai'));
            $role = Role::where('name', $roleName)->first();
            $user->assignRole($role);
            $user->givePermissionTo([
                'dashboard.view',
                'frontdesk.access',
                'backoffice.access',
                'supervision.access',
                'onlineportal.access',
            ]);
        }

        // Create Front Desk Staff
        $frontDeskUsers = [
            [
                'name' => 'Petugas Loket 1',
                'email' => 'loket1@mtsn2malang.sch.id',
                'password' => Hash::make('loket123'),
                'user_type' => 'pegawai',
                'registration_code' => 'LK001',
            ],
            [
                'name' => 'Petugas Loket 2',
                'email' => 'loket2@mtsn2malang.sch.id',
                'password' => Hash::make('loket123'),
                'user_type' => 'pegawai',
                'registration_code' => 'LK002',
            ],
        ];

        foreach ($frontDeskUsers as $userData) {
            $user = User::create(array_merge($userData, [
                'is_active' => true,
                'email_verified_at' => now(),
            ]));

            $role = Role::where('name', 'front_desk')->first();
            $user->assignRole($role);
            $user->givePermissionTo([
                'dashboard.view',
                'frontdesk.access',
                'frontdesk.triage',
                'frontdesk.visitor.manage',
                'frontdesk.service.create',
                'onlineportal.access',
            ]);
        }

        // Create Back Office Staff
        $backOfficeUsers = [
            [
                'name' => 'Staf TU 1',
                'email' => 'staff1@mtsn2malang.sch.id',
                'password' => Hash::make('staff123'),
                'user_type' => 'pegawai',
                'registration_code' => 'ST001',
            ],
            [
                'name' => 'Staf TU 2',
                'email' => 'staff2@mtsn2malang.sch.id',
                'password' => Hash::make('staff123'),
                'user_type' => 'pegawai',
                'registration_code' => 'ST002',
            ],
            [
                'name' => 'Waka Kesiswaan',
                'email' => 'waka.kesiswaan@mtsn2malang.sch.id',
                'password' => Hash::make('waka123'),
                'user_type' => 'pegawai',
                'registration_code' => 'WK001',
            ],
        ];

        foreach ($backOfficeUsers as $userData) {
            $user = User::create(array_merge($userData, [
                'is_active' => true,
                'email_verified_at' => now(),
            ]));

            $role = Role::where('name', 'back_office')->first();
            $user->assignRole($role);
            $user->givePermissionTo([
                'dashboard.view',
                'backoffice.access',
                'backoffice.tickets.view',
                'backoffice.tickets.assign',
                'backoffice.tickets.process',
                'backoffice.tickets.complete',
                'backoffice.reports.view',
            ]);
        }

        // Create Sample Students
        $students = [
            [
                'name' => 'Ahmad Rizki',
                'email' => 'ahmad.rizki@student.mtsn2malang.sch.id',
                'password' => Hash::make('student123'),
                'user_type' => 'siswa',
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@student.mtsn2malang.sch.id',
                'password' => Hash::make('student123'),
                'user_type' => 'siswa',
            ],
        ];

        foreach ($students as $userData) {
            $user = User::create(array_merge($userData, [
                'is_active' => true,
                'email_verified_at' => now(),
            ]));

            $role = Role::where('name', 'siswa')->first();
            $user->assignRole($role);
            $user->givePermissionTo([
                'dashboard.view',
                'onlineportal.access',
                'onlineportal.services.view',
                'onlineportal.services.apply',
                'onlineportal.tickets.view',
                'onlineportal.tickets.track',
            ]);
        }

        // Create Sample Parents
        $parents = [
            [
                'name' => 'Bapak Ahmad Rizki',
                'email' => 'ahmad.rizki.parent@gmail.com',
                'password' => Hash::make('parent123'),
                'user_type' => 'walimurid',
            ],
            [
                'name' => 'Ibu Siti Nurhaliza',
                'email' => 'siti.nurhaliza.parent@gmail.com',
                'password' => Hash::make('parent123'),
                'user_type' => 'walimurid',
            ],
        ];

        foreach ($parents as $userData) {
            $user = User::create(array_merge($userData, [
                'is_active' => true,
                'email_verified_at' => now(),
            ]));

            $role = Role::where('name', 'walimurid')->first();
            $user->assignRole($role);
            $user->givePermissionTo([
                'dashboard.view',
                'onlineportal.access',
                'onlineportal.services.view',
                'onlineportal.services.apply',
                'onlineportal.tickets.view',
                'onlineportal.tickets.track',
            ]);
        }

        // Create Sample Alumni
        $alumni = [
            [
                'name' => 'Muhammad Fadli',
                'email' => 'fadli.alumni@gmail.com',
                'password' => Hash::make('alumni123'),
                'user_type' => 'alumni',
            ],
            [
                'name' => 'Dewi Permata',
                'email' => 'dewi.permata.alumni@gmail.com',
                'password' => Hash::make('alumni123'),
                'user_type' => 'alumni',
            ],
        ];

        foreach ($alumni as $userData) {
            $user = User::create(array_merge($userData, [
                'is_active' => true,
                'email_verified_at' => now(),
            ]));

            $role = Role::where('name', 'alumni')->first();
            $user->assignRole($role);
            $user->givePermissionTo([
                'dashboard.view',
                'onlineportal.access',
                'onlineportal.services.view',
                'onlineportal.services.apply',
                'onlineportal.tickets.view',
                'onlineportal.tickets.track',
            ]);
        }

        // Create Sample Institution Users
        $institutions = [
            [
                'name' => 'SDN Merdeka',
                'email' => 'admin@sdnmerdeka.sch.id',
                'password' => Hash::make('instansi123'),
                'user_type' => 'instansi',
            ],
            [
                'name' => 'Universitas Negeri Malang',
                'email' => 'kerjasama@um.ac.id',
                'password' => Hash::make('instansi123'),
                'user_type' => 'instansi',
            ],
        ];

        foreach ($institutions as $userData) {
            $user = User::create(array_merge($userData, [
                'is_active' => true,
                'email_verified_at' => now(),
            ]));

            $role = Role::where('name', 'instansi')->first();
            $user->assignRole($role);
            $user->givePermissionTo([
                'dashboard.view',
                'onlineportal.access',
                'onlineportal.services.view',
                'onlineportal.services.apply',
                'onlineportal.tickets.view',
                'onlineportal.tickets.track',
            ]);
        }

        // Create Sample Public Users
        $publicUsers = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@email.com',
                'password' => Hash::make('umum123'),
                'user_type' => 'umum',
            ],
            [
                'name' => 'Siti Aminah',
                'email' => 'siti.aminah@email.com',
                'password' => Hash::make('umum123'),
                'user_type' => 'umum',
            ],
        ];

        foreach ($publicUsers as $userData) {
            $user = User::create(array_merge($userData, [
                'is_active' => true,
                'email_verified_at' => now(),
            ]));

            $role = Role::where('name', 'umum')->first();
            $user->assignRole($role);
            $user->givePermissionTo([
                'dashboard.view',
                'onlineportal.access',
                'onlineportal.services.view',
                'onlineportal.services.apply',
                'onlineportal.tickets.view',
                'onlineportal.tickets.track',
            ]);
        }
    }
}