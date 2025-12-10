<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class UserManagementHeading extends Widget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 20;

    protected function getViewData(): array
    {
        return [];
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('filament.widgets.user-management-heading');
    }
}
