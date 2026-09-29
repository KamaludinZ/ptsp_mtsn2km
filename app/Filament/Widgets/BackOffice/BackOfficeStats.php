<?php

namespace App\Filament\Widgets\BackOffice;

use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use App\Support\ServiceMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BackOfficeStats extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 20;

    protected static ?string $pollingInterval = '60s';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasRole('back_office');
    }

    protected function getStats(): array
    {
        $tickets = ServiceMetrics::tickets(now()->startOfYear());
        $mine = Ticket::open()->where('assigned_to_id', auth()->id())->count();
        $unassigned = Ticket::open()->whereNull('assigned_to_id')->count();

        return [
            Stat::make('Tugas saya', $mine)
                ->description('Tiket aktif yang ditugaskan kepada Anda')
                ->descriptionIcon('heroicon-m-user')
                ->color('primary')
                ->url(TicketResource::getUrl('index', ['activeTab' => 'saya'])),
            Stat::make('Belum ditugaskan', $unassigned)
                ->description('Tiket aktif tanpa petugas')
                ->descriptionIcon('heroicon-m-inbox')
                ->color($unassigned ? 'warning' : 'gray')
                ->url(TicketResource::getUrl('index', ['activeTab' => 'antrian'])),
            Stat::make('Melewati target', $tickets['overdue'])
                ->description($tickets['awaiting_approval'] . ' menunggu persetujuan pimpinan')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($tickets['overdue'] ? 'danger' : 'gray')
                ->url(TicketResource::getUrl('index', ['activeTab' => 'terlambat'])),
            Stat::make('Selesai bulan ini', Ticket::where('status', 'completed')->where('actual_completion_date', '>=', now()->startOfMonth())->count())
                ->description('Tepat waktu tahun ini: ' . ($tickets['on_time_rate'] !== null ? $tickets['on_time_rate'] . '%' : '–'))
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
        ];
    }
}
