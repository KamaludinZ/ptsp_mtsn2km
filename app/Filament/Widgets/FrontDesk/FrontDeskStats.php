<?php

namespace App\Filament\Widgets\FrontDesk;

use App\Filament\Resources\TicketResource;
use App\Filament\Resources\VisitorResource;
use App\Models\Ticket;
use App\Support\ServiceMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FrontDeskStats extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 10;

    protected static ?string $pollingInterval = '60s';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasRole('front_desk');
    }

    protected function getStats(): array
    {
        $visitors = ServiceMetrics::visitors();
        $offline = fn () => Ticket::where('mode', 'offline');
        $pickup = $offline()->where('status', 'completed')->where('ready_for_pickup', true)->count();

        return [
            Stat::make('Tamu hari ini', $visitors['today'])
                ->description($visitors['active'] . ' masih di lokasi')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->url(VisitorResource::getUrl('index')),
            Stat::make('Pendaftaran loket hari ini', $offline()->whereDate('created_at', today())->count())
                ->description($offline()->where('status', 'submitted')->count() . ' menunggu verifikasi back office')
                ->descriptionIcon('heroicon-m-document-plus')
                ->color('info'),
            Stat::make('Siap diambil', $pickup)
                ->description('Produk layanan menunggu diserahkan')
                ->descriptionIcon('heroicon-m-hand-raised')
                ->color($pickup ? 'success' : 'gray')
                ->url(TicketResource::getUrl('index', ['activeTab' => 'siap-diambil'])),
            Stat::make('Melewati target', $offline()->overdue()->count())
                ->description('Permohonan loket yang terlambat')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
