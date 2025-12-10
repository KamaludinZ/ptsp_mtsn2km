<?php

require_once 'vendor/autoload.php';

// Create application
$app = require_once 'bootstrap/app.php';

// Start Laravel
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

echo "=== Clearing All Users and Creating Specified Structure ===\n\n";

// Delete all users (except potentially system users)
User::query()->delete();

echo "All users deleted. Now creating users according to specification:\n\n";

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

// Create multiple Back Office users (one for each section including TU)
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

// Create leadership accounts
$admin1 = User::create([
    'name' => 'Admin PTSP',
    'email' => 'ptsp@mtsn2malang.sch.id',
    'password' => Hash::make('admin123'),
    'user_type' => 'pegawai',
    'is_active' => true,
    'email_verified_at' => now(),
]);
$admin1->assignRole('admin');

$admin2 = User::create([
    'name' => 'Administrator',
    'email' => 'admin@mtsn2kotamalang.sch.id',
    'password' => Hash::make('password'),
    'user_type' => 'pegawai',
    'is_active' => true,
    'email_verified_at' => now(),
]);
$admin2->assignRole('admin');

$kepsek = User::create([
    'name' => 'Kepala Sekolah',
    'email' => 'kepsek@mtsn2malang.sch.id',
    'password' => Hash::make('kepsek123'),
    'user_type' => 'pegawai',
    'is_active' => true,
    'email_verified_at' => now(),
]);
$kepsek->assignRole('kepala_sekolah');

$katu = User::create([
    'name' => 'Kepala TU',
    'email' => 'katu@mtsn2malang.sch.id',
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

echo "\nAll users created successfully according to specifications!\n";

echo "\n=== Summary of Created Users ===\n";
echo "Guru: 1 user (Guru Pengajar)\n";
echo "Pegawai: 1 user (Pegawai TU)\n";
echo "Waka: 4 users (Kesiswaan, Kurikulum, Humas, Sarpras)\n";
echo "Back Office: 5 users (one for each section)\n";
echo "Front Desk: 3 users (Loket 1, 2, 3)\n";
echo "Leadership: 4 users (Admin x2, Kepsek, Katu)\n";
echo "Total: " . User::count() . " users\n";