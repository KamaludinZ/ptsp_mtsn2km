<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class SurveyFeedbackHeading extends Widget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 60;

    protected static bool $isLazy = false;

    public static function getView(): string
    {
        return 'filament.widgets.survey-feedback-heading';
    }
}
