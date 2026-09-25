<?php

if (!function_exists('get_dashboard_route_for_user')) {
    /**
     * Dashboard URL for a user based on their role (single source of truth,
     * also used by DashboardController after login).
     */
    function get_dashboard_route_for_user(\App\Models\User $user): string
    {
        return match (true) {
            // Administrators work in the Filament control panel
            $user->hasRole('admin') => '/cp',
            $user->hasAnyRole(['kepala_sekolah', 'kepala_tu']) => '/pimpinan',
            $user->hasRole('back_office') => '/backoffice/dashboard',
            $user->hasRole('front_desk') => '/frontdesk/dashboard',
            $user->hasRole('supervisor') => '/supervision/management',
            // Applicants: guru, pegawai, siswa, wali murid, alumni, instansi, umum
            default => '/portal/dashboard',
        };
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
