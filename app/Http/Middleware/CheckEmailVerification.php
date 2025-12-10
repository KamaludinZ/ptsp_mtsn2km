<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckEmailVerification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip this middleware for Filament admin panel routes
        if ($request->is('admin/*') || $request->is('admin') || $request->is('filament/*')) {
            return $next($request);
        }

        if (!Auth::check()) {
            return redirect()->guest(route('login'));
        }

        $user = Auth::user();

        // Check if user exists and has roles method
        if (!$user || !method_exists($user, 'hasRole')) {
            return redirect()->guest(route('login'));
        }

        // If user has 'pemohon' role, they must have verified email
        if ($user->hasRole('pemohon')) {
            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }
        }

        return $next($request);
    }
}
