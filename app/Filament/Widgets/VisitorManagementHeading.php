<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class VisitorManagementHeading extends Widget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 40;

    protected static bool $isLazy = false;

    public static function getView(): string
    {
        return 'filament.widgets.visitor-management-heading';
    }
}
