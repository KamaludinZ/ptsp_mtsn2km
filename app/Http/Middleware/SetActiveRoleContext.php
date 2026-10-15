<?php

namespace App\Http\Middleware;

use App\Support\ActiveRoles;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menetapkan konteks peran aktif petugas untuk request ini
 * (ActiveRoles::inContext) dan menyimpannya di sesi. Petugas multi-peran
 * tidak perlu memilih dulu: peran terakhirnya (atau peran pertamanya)
 * langsung aktif, dan ia bisa berganti peran di header.
 * Pemohon dan tamu dilewatkan apa adanya.
 */
class SetActiveRoleContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user instanceof \App\Models\User || ! $user->isStaff()) {
            return $next($request);
        }

        $session = $request->hasSession() ? $request->session() : null;
        $role = ActiveRoles::resolve($user, $session?->get(ActiveRoles::SESSION_KEY));

        if ($session) {
            $role === null ? $session->forget(ActiveRoles::SESSION_KEY) : $session->put(ActiveRoles::SESSION_KEY, $role);
            $session->forget(ActiveRoles::PICK_FLAG);
        }
        $request->attributes->set(ActiveRoles::REQUEST_ATTRIBUTE, $role);

        return $next($request);
    }
}
