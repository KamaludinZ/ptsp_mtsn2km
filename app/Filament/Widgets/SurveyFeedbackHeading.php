<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class SurveyFeedbackHeading extends Widget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 60;

    protected function getViewData(): array
    {
        return [];
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('filament.widgets.survey-feedback-heading');
    }
}
