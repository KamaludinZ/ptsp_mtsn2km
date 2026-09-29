<?php

namespace App\Filament\Portal\Pages;

use App\Filament\Portal\Widgets;
use Filament\Pages\Dashboard as BaseDashboard;

/** The applicant's overview: their requests, finished results and surveys to fill in. */
class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Beranda';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getTitle(): string
    {
        return 'Halo, ' . auth()->user()->name;
    }

    public function getSubheading(): ?string
    {
        return 'Pantau permohonan layanan Anda di sini.';
    }

    public function getWidgets(): array
    {
        return [
            Widgets\ApplicantStats::class,
            Widgets\SurveyReminder::class,
            Widgets\ReadyResults::class,
            Widgets\RecentRequests::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return 1;
    }
}
