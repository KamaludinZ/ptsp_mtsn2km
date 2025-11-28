<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the appropriate dashboard based on user role
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Route to appropriate dashboard based on roles/permissions
        if ($user->hasAnyRole(['admin', 'super_admin'])) {
            return redirect('/admin');
        } elseif ($user->hasRole('kepala-sekolah')) {
            return redirect('/kepala');
        } elseif ($user->hasRole('kepala-tu')) {
            return redirect('/katu');
        } elseif ($user->hasAnyRole(['waka-kesiswaan', 'waka-kurikulum', 'waka-sarpras', 'waka-humas'])) {
            return redirect('/waka');
        } elseif ($user->hasRole('tu')) {
            return redirect('/back');
        } elseif ($user->hasRole('petugas_loket')) {
            return redirect('/front');
        } else {
            // Default for regular users (guru, pegawai, siswa, wali murid, alumni, instansi, umum)
            return redirect('/portal/dashboard');
        }
    }
}
