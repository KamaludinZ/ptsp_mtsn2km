<?php

namespace App\Http\Middleware;

use App\Support\Impersonation;
use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menjaga sesi ganti akun sementara di setiap request: sesi yang lognya
 * hilang/tertutup, administratornya tidak lagi berwenang, atau melewati
 * batas waktu diakhiri dan dikembalikan ke akun administrator.
 */
class GuardImpersonation
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasSession() || ! Impersonation::active()) {
            return $next($request);
        }

        if ($why = Impersonation::expiryReason()) {
            Impersonation::end('kedaluwarsa', $why);

            Notification::make()->warning()->title('Sesi ganti akun diakhiri')->body($why)->send();

            return $request->expectsJson() || $request->hasHeader('X-Livewire')
                ? response()->json(['message' => $why], 409)
                : redirect(auth()->check() ? get_dashboard_route_for_user(auth()->user()) : '/login');
        }

        return $next($request);
    }
}
