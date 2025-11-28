<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\LatestTicketsWidget;
use App\Filament\Widgets\LatestComplaintsWidget;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\SystemInfoWidget;

class Dashboard extends BaseDashboard
{
    public function getHeaderWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int
    {
        return 2;
    }

    public function getWidgets(): array
    {
        return [
            RecentActivityWidget::class,
            SystemInfoWidget::class,
            LatestTicketsWidget::class,
            LatestComplaintsWidget::class,
            // WeeklyPerformanceChart::class,
        ];
    }

    public function getColumns(): int
    {
        return 1;
    }
}