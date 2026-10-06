<?php

namespace App\Filament\Concerns;

use App\Exceptions\TicketActionException;
use Filament\Notifications\Notification;

/**
 * Runs a service call from a Filament action and reports the outcome as a
 * notification; business-rule refusals are shown to the officer as-is.
 */
trait NotifiesActionResult
{
    protected static function attempt(callable $callback, string $success): bool
    {
        try {
            $callback();
        } catch (\App\Exceptions\OutsideActiveRoleException $e) {
            \App\Support\ActiveRoleToast::outsideRole($e);

            return false;
        } catch (TicketActionException $e) {
            Notification::make()->title('Tidak dapat diproses')->body($e->getMessage())->danger()->send();

            return false;
        }

        Notification::make()->title($success)->success()->send();

        return true;
    }
}
