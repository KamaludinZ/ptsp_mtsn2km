<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class ServicesTicketingHeading extends Widget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 30;

    protected function getViewData(): array
    {
        return [];
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('filament.widgets.services-ticketing-heading');
    }
}
