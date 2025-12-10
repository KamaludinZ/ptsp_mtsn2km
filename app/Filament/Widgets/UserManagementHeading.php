<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class UserManagementHeading extends Widget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 20;

    protected static bool $isLazy = false;

    public static function getView(): string
    {
        return 'filament.widgets.user-management-heading';
    }
}
