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
        return redirect(get_dashboard_route_for_user($request->user()));
    }
}
