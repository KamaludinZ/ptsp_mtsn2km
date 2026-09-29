<?php

if (!function_exists('get_dashboard_route_for_user')) {
    /**
     * Landing page after sign-in (single source of truth, also used by
     * DashboardController): staff work in the control panel, applicants
     * (guru, pegawai, siswa, wali murid, alumni, instansi, umum) in the portal.
     */
    function get_dashboard_route_for_user(\App\Models\User $user): string
    {
        return $user->isStaff() ? '/cp' : '/portal';
    }
}

if (!function_exists('app_brand_name')) {
    /**
     * Application name shown in headers, footers and titles. Editable by
     * admins through the "app_name" setting (Panel Kontrol > Pengaturan).
     */
    function app_brand_name(): string
    {
        return trim((string) config('app.app_name')) ?: 'PTSP MTsN 2 KOTA MALANG';
    }
}
