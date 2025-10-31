<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use App\Models\Service;
use App\Models\Visitor;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Layanan', Service::count())
                ->description('Jenis layanan tersedia')
                ->descriptionIcon('heroicon-o-clipboard-document-list')
                ->color('success')
                ->chart([7, 12, 15, 18, 20, 25, Service::count()]),

            Stat::make('Tiket Aktif', Ticket::whereIn('status', ['pending', 'processing', 'verified'])->count())
                ->description('Sedang diproses')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning')
                ->chart([5, 10, 8, 12, 15, 18, 20]),

            Stat::make('Tiket Selesai', Ticket::where('status', 'completed')->count())
                ->description('Bulan ini')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success')
                ->chart([10, 15, 20, 25, 30, 35, 40]),

            Stat::make('Pengunjung Hari Ini', Visitor::whereDate('checked_in_at', today())->count())
                ->description('Tamu fisik')
                ->descriptionIcon('heroicon-o-users')
                ->color('info')
                ->chart([2, 5, 7, 10, 8, 12, 15]),
        ];
    }
}
