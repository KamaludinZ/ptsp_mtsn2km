<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class ComplaintsReportingHeading extends Widget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 50;

    protected static bool $isLazy = false;

    public static function getView(): string
    {
        return 'filament.widgets.complaints-reporting-heading';
    }
}
