<?php

namespace App\Filament\Resources\Pages;

use Filament\Pages\Page;
use Filament\Widgets;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\LatestTicketsWidget;
use App\Filament\Widgets\LatestComplaintsWidget;
// use App\Filament\Widgets\WeeklyPerformanceChart;

class Dashboard extends Page
{
    protected static string $view = 'filament.pages.dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            LatestTicketsWidget::class,
            LatestComplaintsWidget::class,
            // WeeklyPerformanceChart::class,
        ];
    }

}