<?php

namespace App\Http\Middleware;

use App\Filament\Pages\ChooseActiveRole;
use App\Support\ActiveRoles;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menetapkan konteks peran aktif petugas untuk request ini
 * (ActiveRoles::inContext) dan menyimpannya di sesi. Petugas multi-peran
 * yang belum memilih, atau baru masuk dan belum memilih di sesi ini,
 * diarahkan ke halaman Pilih Peran Aktif saat membuka halaman /cp;
 * setelah memilih ia kembali ke halaman yang dituju.
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
        }
        $request->attributes->set(ActiveRoles::REQUEST_ATTRIBUTE, $role);

        // Belum ada peran sama sekali, atau sesi baru yang wajib memilih (ActiveRoles::startSession).
        $mustPick = $session && ($session->get(ActiveRoles::PICK_FLAG) ? ActiveRoles::for($user)->count() > 1 : ($role === null && ActiveRoles::needsChoice($user)));

        if ($mustPick && $this->opensAPage($request)) {
            $session->put('url.intended', $request->fullUrl());

            return redirect()->to(ChooseActiveRole::getUrl(panel: 'admin'));
        }

        return $next($request);
    }

    /** Halaman biasa /cp (bukan halaman pemilih itu sendiri, bukan aksi Livewire/JSON). */
    private function opensAPage(Request $request): bool
    {
        return $request->isMethod('GET')
            && $request->routeIs('filament.admin.*')
            && ! $request->hasHeader('X-Livewire')
            && ! $request->expectsJson()
            && ! $request->routeIs(ChooseActiveRole::getRouteName('admin'));
    }
}
