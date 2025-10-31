<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use App\Models\Service;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ParentStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $userId = auth()->id();

        // Filter services available for parents
        $availableServices = Service::where(function($query) {
            $query->where('user_types', 'like', '%walimurid%')
                  ->orWhere('user_types', 'like', '%all%');
        })->count();

        return [
            Stat::make('Layanan Untuk Wali Murid', $availableServices)
                ->description('Layanan tersedia')
                ->descriptionIcon('heroicon-o-clipboard-document-list')
                ->color('info'),

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
