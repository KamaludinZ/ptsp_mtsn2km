<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\TicketResource;
use App\Support\ServiceMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * "Layanan Hari Ini": permohonan masuk dan selesai hari ini. Shown at the top
 * of every staff role's dashboard so everyone reads the same numbers.
 */
class TodaySummary extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 0;

    protected static ?string $pollingInterval = '60s';

    protected ?string $heading = 'Layanan Hari Ini';

    public static function canView(): bool
    {
        return auth()->check();
    }

    protected function getDescription(): ?string
    {
        return now()->translatedFormat('l, j F Y') . ' · diperbarui pukul ' . now()->format('H:i');
    }

    protected function getStats(): array
    {
        $counts = ServiceMetrics::today();
        $today = array_map(fn (int $n) => number_format($n, 0, ',', '.'), $counts);
        $quiet = $counts['in'] === 0;
        $tickets = fn (array $query = []) => TicketResource::canViewAny() ? TicketResource::getUrl('index', $query) : null;

        return [
            Stat::make('Permohonan masuk', $today['in'])
                ->description($quiet ? 'Belum ada permohonan masuk hari ini' : "{$today['in_online']} online · {$today['in_offline']} loket")
                ->descriptionIcon($quiet ? 'heroicon-m-inbox' : 'heroicon-m-inbox-arrow-down')
                ->color($quiet ? 'gray' : 'info')
                ->url($tickets()),
            Stat::make('Selesai hari ini', $today['completed'])
                ->description($counts['completed'] + $counts['rejected'] === 0 ? 'Belum ada permohonan ditutup hari ini' : $today['rejected'] . ' ditolak hari ini')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            Stat::make('Masih diproses', $today['open'])
                ->description('Seluruh permohonan yang belum ditutup')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning'),
            Stat::make('Melewati target', $today['overdue'])
                ->description('Permohonan terlambat dari standar layanan')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($counts['overdue'] ? 'danger' : 'gray'),
        ];
    }
}
