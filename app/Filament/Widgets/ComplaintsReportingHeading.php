<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class ComplaintsReportingHeading extends Widget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 50;

    protected function getViewData(): array
    {
        return [];
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('filament.widgets.complaints-reporting-heading');
    }
}
