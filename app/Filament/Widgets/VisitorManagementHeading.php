<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class VisitorManagementHeading extends Widget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 40;

    protected static bool $isLazy = false;

    protected static string $view = 'filament.widgets.visitor-management-heading';
}
