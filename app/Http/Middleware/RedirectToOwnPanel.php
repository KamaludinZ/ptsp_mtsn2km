<?php

namespace App\Http\Middleware;

use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Notifications\Notification;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Filament's panel authentication, plus: a signed-in user who opens the
 * other panel (an applicant at /cp, staff at /portal) is sent to their own
 * panel instead of a 403. Deactivated accounts still get the 403 page.
 */
class RedirectToOwnPanel extends Authenticate
{
    protected function authenticate($request, array $guards): void
    {
        $user = Filament::auth()->user();
        $panel = Filament::getCurrentPanel();

        if ($user && $panel && $user->is_active !== false && ! $user->canAccessPanel($panel)) {
            Notification::make()
                ->title('Anda diarahkan ke dasbor Anda')
                ->body('Halaman tadi untuk ' . ($user->isStaff() ? 'pemohon' : 'petugas') . '.')
                ->info()
                ->send();

            throw new HttpResponseException(redirect(get_dashboard_route_for_user($user)));
        }

        parent::authenticate($request, $guards);
    }
}
