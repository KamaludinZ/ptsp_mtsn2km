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
        if ($user->hasRole('admin')) {
            return redirect('/admin');
        } elseif ($user->hasAnyRole(['kepala_sekolah', 'kepala_tu'])) {
            return redirect('/backoffice/dashboard');
        } elseif ($user->hasRole('back_office')) {
            return redirect('/backoffice/dashboard');
        } elseif ($user->hasRole('front_desk')) {
            return redirect('/frontdesk/dashboard');
        } elseif ($user->hasRole('supervisor')) {
            return redirect('/supervision/management');
        } else {
            // Default for regular users (guru, pegawai, siswa, wali murid, alumni, instansi, umum)
            return redirect('/portal/dashboard');
        }
    }
}
