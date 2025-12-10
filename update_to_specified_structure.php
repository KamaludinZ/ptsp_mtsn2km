<?php

require_once 'vendor/autoload.php';

// Create application
$app = require_once 'bootstrap/app.php';

// Start Laravel
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

echo "=== Updating Database to Match Specified Structure ===\n\n";

// First, let's create the additional users according to your specification

// Remove existing non-compliant users (keeping only original admin accounts)
$usersToDelete = [
    'Super Admin PTSP', // This is the old one we don't want
    'Admin PTSP', // We'll keep the new admin accounts
    'Kepala Sekolah',
    'Kepala TU',
    'Petugas Loket 1', 
    'Petugas Loket 2',
    'Staf TU 1',
    'Staf TU 2',
    'Waka Kesiswaan',
    'Ahmad Rizki',
    'Siti Nurhaliza',
    'Bapak Ahmad Rizki',
    'Ibu Siti Nurhaliza',
    'Muhammad Fadli',
    'Dewi Permata',
    'SDN Merdeka',
    'Universitas Negeri Malang',
    'Budi Santoso',
    'Siti Aminah'
];
// Don't delete the proper admin account

foreach ($usersToDelete as $userName) {
    $user = User::where('name', $userName)->first();
    if ($user) {
        $user->roles()->detach();
        $user->delete();
        echo "Deleted user: {$userName}\n";
    }
}

echo "\nCreating new users based on specifications:\n\n";

// Create Guru user
$guru = User::create([
    'name' => 'Guru Pengajar',
    'email' => 'guru@mtsn2malang.sch.id',
    'password' => Hash::make('guru123'),
    'user_type' => 'guru',
    'is_active' => true,
    'email_verified_at' => now(),
]);
$guru->assignRole('gtk');
echo "Created Guru: {$guru->name} ({$guru->email})\n";

// Create Pegawai user
$pegawai = User::create([
    'name' => 'Pegawai TU',
    'email' => 'pegawai@mtsn2malang.sch.id',
    'password' => Hash::make('pegawai123'),
    'user_type' => 'pegawai',
    'is_active' => true,
    'email_verified_at' => now(),
]);
$pegawai->assignRole('gtk');
echo "Created Pegawai: {$pegawai->name} ({$pegawai->email})\n";

// Create multiple Waka users
$wakas = [
    ['name' => 'Waka Kesiswaan', 'email' => 'waka.kesiswaan@mtsn2malang.sch.id'],
    ['name' => 'Waka Kurikulum', 'email' => 'waka.kurikulum@mtsn2malang.sch.id'],
    ['name' => 'Waka Humas', 'email' => 'waka.humas@mtsn2malang.sch.id'],
    ['name' => 'Waka Sarpras', 'email' => 'waka.sarpras@mtsn2malang.sch.id'],
];

foreach ($wakas as $waka) {
    $user = User::create([
        'name' => $waka['name'],
        'email' => $waka['email'],
        'password' => Hash::make('waka123'),
        'user_type' => 'pegawai',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $user->assignRole('waka');
    echo "Created Waka: {$user->name} ({$user->email})\n";
}

// Create multiple Back Office users (one for each waka section)
$backOffices = [
    ['name' => 'Staf Waka TU', 'email' => 'staf.katu@mtsn2malang.sch.id'],
    ['name' => 'Staf Waka Kurikulum', 'email' => 'staf.kurikulum@mtsn2malang.sch.id'],
    ['name' => 'Staf Waka Kesiswaan', 'email' => 'staf.kesiswaan@mtsn2malang.sch.id'],
    ['name' => 'Staf Waka Humas', 'email' => 'staf.humas@mtsn2malang.sch.id'],
    ['name' => 'Staf Waka Sarpras', 'email' => 'staf.sarpras@mtsn2malang.sch.id'],
];

foreach ($backOffices as $backOffice) {
    $user = User::create([
        'name' => $backOffice['name'],
        'email' => $backOffice['email'],
        'password' => Hash::make('staff123'),
        'user_type' => 'pegawai',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $user->assignRole('back_office');
    echo "Created Back Office: {$user->name} ({$user->email})\n";
}

// Create 3 Front Desk users
$frontDesks = [
    ['name' => 'Petugas Loket 1', 'email' => 'loket1@mtsn2malang.sch.id'],
    ['name' => 'Petugas Loket 2', 'email' => 'loket2@mtsn2malang.sch.id'],
    ['name' => 'Petugas Loket 3', 'email' => 'loket3@mtsn2malang.sch.id'],
];

foreach ($frontDesks as $frontDesk) {
    $user = User::create([
        'name' => $frontDesk['name'],
        'email' => $frontDesk['email'],
        'password' => Hash::make('loket123'),
        'user_type' => 'pegawai',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $user->assignRole('front_desk');
    echo "Created Front Desk: {$user->name} ({$user->email})\n";
}

// Keep original admin accounts
$admin1 = User::firstOrCreate([
    'email' => 'ptsp@mtsn2malang.sch.id'
], [
    'name' => 'Admin PTSP',
    'password' => Hash::make('admin123'),
    'user_type' => 'pegawai',
    'is_active' => true,
    'email_verified_at' => now(),
]);
$admin1->assignRole('admin');

$admin2 = User::firstOrCreate([
    'email' => 'admin@mtsn2kotamalang.sch.id'
], [
    'name' => 'Administrator',
    'password' => Hash::make('password'),
    'user_type' => 'pegawai',
    'is_active' => true,
    'email_verified_at' => now(),
]);
$admin2->assignRole('admin');

// Create kepala_sekolah user
$kepsek = User::firstOrCreate([
    'email' => 'kepsek@mtsn2malang.sch.id'
], [
    'name' => 'Kepala Sekolah',
    'password' => Hash::make('kepsek123'),
    'user_type' => 'pegawai',
    'is_active' => true,
    'email_verified_at' => now(),
]);
$kepsek->assignRole('kepala_sekolah');

// Create kepala_tu user
$katu = User::firstOrCreate([
    'email' => 'katu@mtsn2malang.sch.id'
], [
    'name' => 'Kepala TU',
    'password' => Hash::make('katu123'),
    'user_type' => 'pegawai',
    'is_active' => true,
    'email_verified_at' => now(),
]);
$katu->assignRole('kepala_tu');

echo "\nCreated leadership accounts:\n";
echo "- Kepala Sekolah: kepsek@mtsn2malang.sch.id\n";
echo "- Kepala TU: katu@mtsn2malang.sch.id\n";
echo "- Admin PTSP: ptsp@mtsn2malang.sch.id\n";
echo "- Administrator: admin@mtsn2kotamalang.sch.id\n";

echo "\nRole structure update completed!\n";