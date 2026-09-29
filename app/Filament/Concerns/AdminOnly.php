<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Master data and system settings: the staff panel is shared by every staff
 * role, but only administrators may open or change these resources.
 */
trait AdminOnly
{
    public static function can(string $action, ?Model $record = null): bool
    {
        return (bool) auth()->user()?->hasRole('admin') && parent::can($action, $record);
    }
}
