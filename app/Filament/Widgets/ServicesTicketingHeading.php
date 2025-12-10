<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class ServicesTicketingHeading extends Widget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 30;

    protected static bool $isLazy = false;

    public static function getView(): string
    {
        return 'filament.widgets.services-ticketing-heading';
    }
}
