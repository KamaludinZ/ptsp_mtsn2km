<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StaffStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $userId = auth()->id();

        return [
            Stat::make('Permohonan Saya', Ticket::where('user_id', $userId)->count())
                ->description('Total permohonan')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary'),

            Stat::make('Sedang Diproses', Ticket::where('user_id', $userId)
                ->whereIn('status', ['pending', 'processing', 'verified'])
                ->count())
                ->description('Menunggu penyelesaian')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Selesai', Ticket::where('user_id', $userId)
                ->where('status', 'completed')
                ->count())
                ->description('Permohonan selesai')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}
