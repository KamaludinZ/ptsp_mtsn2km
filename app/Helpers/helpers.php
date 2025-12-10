<?php

use App\Helpers\AssetHelper;

if (!function_exists('asset_css')) {
    /**
     * Generate CSS link tag for compiled asset
     *
     * @param string $path Path to CSS file
     * @return string HTML link tag
     */
    function asset_css($path)
    {
        return AssetHelper::css($path);
    }
}

if (!function_exists('asset_js')) {
    /**
     * Generate JS script tag for compiled asset
     *
     * @param string $path Path to JS file
     * @param bool $defer Add defer attribute
     * @return string HTML script tag
     */
    function asset_js($path, $defer = true)
    {
        return AssetHelper::js($path, $defer);
    }
}

if (!function_exists('assets_preload')) {
    /**
     * Generate preload links for critical assets
     *
     * @param array $paths Array of asset paths
     * @return string HTML preload links
     */
    function assets_preload($paths)
    {
        return AssetHelper::preload($paths);
    }
}

if (!function_exists('compiled_asset')) {
    /**
     * Get URL to compiled asset
     *
     * @param string $path Path to asset
     * @return string Full URL to compiled asset
     */
    function compiled_asset($path)
    {
        return AssetHelper::asset($path);
    }
}

if (!function_exists('get_dashboard_route_for_user')) {
    /**
     * Get the appropriate dashboard route for a given user based on their roles.
     *
     * @param \App\Models\User $user
     * @return string
     */
    function get_dashboard_route_for_user(\App\Models\User $user)
    {
        if ($user->hasRole('admin')) {
            return '/admin';
        } elseif ($user->hasRole('kepala-sekolah')) {
            return '/kepala';
        } elseif ($user->hasRole('kepala-tu')) {
            return '/katu';
        } elseif ($user->hasAnyRole(['waka-kesiswaan', 'waka-kurikulum', 'waka-sarpras', 'waka-humas'])) {
            return '/waka';
        } elseif ($user->hasRole('tu')) {
            return '/back';
        } elseif ($user->hasRole('petugas_loket')) {
            return '/front';
        } else {
            // Default for regular users (guru, pegawai, siswa, wali murid, alumni, instansi, umum)
            return '/portal/dashboard';
        }
    }
}
