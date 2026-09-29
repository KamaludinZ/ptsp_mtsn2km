<?php

namespace App\Filament\Portal\Widgets;

use App\Models\Ticket;
use Filament\Widgets\Widget;

/** Completed services the applicant has not rated yet (SKM/SPAK, Modul 11). */
class SurveyReminder extends Widget
{
    protected static bool $isDiscovered = false;

    protected static string $view = 'filament.portal.widgets.survey-reminder';

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return static::unrated()->exists();
    }

    private static function unrated()
    {
        return Ticket::where('user_id', auth()->id())
            ->where('status', 'completed')
            ->whereDoesntHave('surveyResponses');
    }

    protected function getViewData(): array
    {
        $first = static::unrated()->latest('actual_completion_date')->first();

        return [
            'count' => static::unrated()->count(),
            'url' => route('survey.form', ['tiket' => $first?->ticket_number]),
        ];
    }
}
