<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use App\Models\Service;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StudentStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $userId = auth()->id();
        $userType = auth()->user()->user_type;

        // Filter services available for students/alumni
        $availableServices = Service::where(function($query) use ($userType) {
            $query->where('user_types', 'like', "%{$userType}%")
                  ->orWhere('user_types', 'like', '%all%');
        })->count();

        return [
            Stat::make('Layanan Tersedia', $availableServices)
                ->description('Layanan untuk ' . ($userType === 'siswa' ? 'siswa' : 'alumni'))
                ->descriptionIcon('heroicon-o-clipboard-document-list')
                ->color('info'),

            Stat::make('Permohonan Aktif', Ticket::where('user_id', $userId)
                ->whereIn('status', ['pending', 'processing', 'verified'])
                ->count())
                ->description('Sedang diproses')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Permohonan Selesai', Ticket::where('user_id', $userId)
                ->where('status', 'completed')
                ->count())
                ->description('Sudah selesai')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}
