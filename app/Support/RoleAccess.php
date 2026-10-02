<?php

namespace App\Support;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Which staff area each role may open. Permissions belong to roles (not to
 * individual users), so a staff account created from the admin panel gets
 * the right menus and access as soon as it is given its role.
 */
class RoleAccess
{
    public const AREA_PERMISSIONS = ['frontdesk.access', 'backoffice.access', 'supervision.access'];

    public const ROLE_PERMISSIONS = [
        'admin' => ['frontdesk.access', 'backoffice.access', 'supervision.access'],
        'kepala_sekolah' => ['backoffice.access', 'supervision.access'],
        'kepala_tu' => ['frontdesk.access', 'backoffice.access', 'supervision.access'],
        'back_office' => ['backoffice.access'],
        'front_desk' => ['frontdesk.access'],
        'supervisor' => ['supervision.access'],
    ];

    /** Roles that follow up complaints and whistleblowing reports (Modul 10) and read survey reports. */
    public const COMPLAINT_HANDLERS = ['admin', 'supervisor', 'kepala_sekolah', 'kepala_tu'];

    /** Roles that approve service requests (Modul 8) and see the executive dashboard (Modul 13). */
    public const LEADERSHIP = ['admin', 'kepala_sekolah', 'kepala_tu'];

    /**
     * Roles the application itself relies on (staff areas, account types);
     * they can be edited but not renamed or deleted.
     */
    public const SYSTEM_ROLES = [
        'admin' => 'Administrator',
        'kepala_sekolah' => 'Kepala Madrasah',
        'kepala_tu' => 'Kepala Tata Usaha',
        'supervisor' => 'Pengawas',
        'back_office' => 'Back Office',
        'front_desk' => 'Front Desk',
        'guru' => 'Guru',
        'pegawai' => 'Pegawai',
        'siswa' => 'Siswa',
        'walimurid' => 'Wali Murid',
        'alumni' => 'Alumni',
        'instansi' => 'Instansi/Perusahaan',
        'umum' => 'Masyarakat Umum',
    ];

    /** Plain-language description of each permission, grouped by the area it opens. */
    public const PERMISSION_LABELS = [
        'dashboard.view' => 'Dasbor — lihat dasbor',
        'dashboard.admin' => 'Dasbor — dasbor administrator',
        'frontdesk.access' => 'Front Desk — buka area front desk',
        'frontdesk.triage' => 'Front Desk — verifikasi permohonan masuk',
        'frontdesk.visitor.manage' => 'Front Desk — kelola buku tamu',
        'frontdesk.service.create' => 'Front Desk — daftarkan layanan offline',
        'backoffice.access' => 'Back Office — buka area back office',
        'backoffice.tickets.view' => 'Back Office — lihat tiket',
        'backoffice.tickets.assign' => 'Back Office — tugaskan tiket',
        'backoffice.tickets.process' => 'Back Office — proses tiket',
        'backoffice.tickets.complete' => 'Back Office — selesaikan tiket',
        'backoffice.reports.view' => 'Back Office — lihat laporan',
        'supervision.access' => 'Pengawasan — buka area pengawasan',
        'supervision.complaints.view' => 'Pengawasan — lihat pengaduan',
        'supervision.complaints.process' => 'Pengawasan — tindak lanjuti pengaduan',
        'supervision.surveys.view' => 'Pengawasan — lihat hasil survei',
        'supervision.surveys.manage' => 'Pengawasan — kelola survei',
        'supervision.performance.view' => 'Pengawasan — lihat kinerja layanan',
        'onlineportal.access' => 'Portal Online — buka portal',
        'onlineportal.services.view' => 'Portal Online — lihat layanan',
        'onlineportal.services.apply' => 'Portal Online — ajukan layanan',
        'onlineportal.tickets.view' => 'Portal Online — lihat tiket sendiri',
        'onlineportal.tickets.track' => 'Portal Online — lacak tiket',
        'users.view' => 'Pengguna — lihat',
        'users.create' => 'Pengguna — tambah',
        'users.edit' => 'Pengguna — ubah',
        'users.delete' => 'Pengguna — hapus',
        'services.view' => 'Layanan — lihat',
        'services.create' => 'Layanan — tambah',
        'services.edit' => 'Layanan — ubah',
        'services.delete' => 'Layanan — hapus',
        'settings.view' => 'Pengaturan — lihat',
        'settings.edit' => 'Pengaturan — ubah',
    ];

    public static function roleLabel(string $role): string
    {
        return self::SYSTEM_ROLES[$role] ?? ucwords(str_replace('_', ' ', $role));
    }

    public static function permissionLabel(string $permission): string
    {
        return self::PERMISSION_LABELS[$permission] ?? $permission;
    }

    public static function isSystemRole(?string $role): bool
    {
        return $role !== null && array_key_exists($role, self::SYSTEM_ROLES);
    }

    /** Area permissions a system role always keeps, whatever is unticked in the form. */
    public static function requiredPermissions(?string $role): array
    {
        return self::ROLE_PERMISSIONS[$role] ?? [];
    }

    public static function sync(): void
    {
        foreach (self::AREA_PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (self::ROLE_PERMISSIONS as $role => $permissions) {
            Role::findOrCreate($role, 'web')->givePermissionTo($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
