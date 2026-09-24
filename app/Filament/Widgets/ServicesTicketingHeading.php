<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class ServicesTicketingHeading extends Widget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 30;

    protected static bool $isLazy = false;

    protected static string $view = 'filament.widgets.services-ticketing-heading';
}
