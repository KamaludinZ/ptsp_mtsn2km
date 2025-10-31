<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use App\Models\Service;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PublicStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $userId = auth()->id();
        $userType = auth()->user()->user_type;

        // Filter services available for public/institutions
        $availableServices = Service::where(function($query) use ($userType) {
            $query->where('user_types', 'like', "%{$userType}%")
                  ->orWhere('user_types', 'like', '%all%');
        })->count();

        return [
            Stat::make('Layanan Publik', $availableServices)
                ->description('Layanan tersedia')
                ->descriptionIcon('heroicon-o-clipboard-document-list')
                ->color('info'),

            Stat::make('Permohonan Saya', Ticket::where('user_id', $userId)->count())
                ->description('Total permohonan')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary'),

            Stat::make('Dalam Proses', Ticket::where('user_id', $userId)
                ->whereIn('status', ['pending', 'processing', 'verified'])
                ->count())
                ->description('Sedang diproses')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),
        ];
    }
}
