<?php

namespace App\Filament\Portal\Widgets;

use App\Filament\Portal\Resources\TicketResource;
use App\Support\ServiceMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ApplicantStats extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $stats = ServiceMetrics::tickets(userId: auth()->id());

        return [
            Stat::make('Total permohonan', $stats['total'])
                ->descriptionIcon('heroicon-m-inbox-stack')
                ->description('Semua permohonan Anda')
                ->color('primary')
                ->url(TicketResource::getUrl('index')),
            Stat::make('Sedang diproses', $stats['open'])
                ->description($stats['awaiting_approval'] . ' menunggu persetujuan pimpinan')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning'),
            Stat::make('Selesai', $stats['completed'])
                ->description('Permohonan yang sudah selesai')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
        ];
    }
}
