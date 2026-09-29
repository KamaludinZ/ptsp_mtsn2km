<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class VisitorManagementHeading extends Widget
{
    protected static bool $isDiscovered = false;

    /** Part of the administrator's dashboard. */
    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasRole('admin');
    }

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 40;

    protected static bool $isLazy = false;

    protected static string $view = 'filament.widgets.visitor-management-heading';
}
