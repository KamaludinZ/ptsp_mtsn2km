<?php

namespace App\Filament\Widgets\Leadership;

use App\Filament\Pages\Leadership\Approvals;
use App\Filament\Pages\Reports\Performance;
use App\Models\Ticket;
use App\Support\RoleAccess;
use App\Support\ServiceMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LeadershipStats extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = null;

    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasAnyRole(array_diff(RoleAccess::LEADERSHIP, ['admin']));
    }

    protected function getStats(): array
    {
        $myApprovals = Ticket::approvableBy(auth()->user())->count();
        $tickets = ServiceMetrics::tickets(now()->startOfYear());
        $survey = ServiceMetrics::survey(now()->startOfYear());

        return [
            Stat::make('Menunggu keputusan Anda', $myApprovals)
                ->description($myApprovals ? 'Buka menu Disposisi Masuk' : 'Tidak ada yang menunggu')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color($myApprovals ? 'warning' : 'success')
                ->url(Approvals::getUrl()),
            Stat::make('Permohonan tahun ini', $tickets['total'])
                ->description($tickets['open'] . ' masih diproses')
                ->descriptionIcon('heroicon-m-inbox-stack')
                ->color('primary')
                ->url(Performance::getUrl()),
            Stat::make('Tepat waktu', $tickets['on_time_rate'] !== null ? $tickets['on_time_rate'] . '%' : '–')
                ->description($tickets['overdue'] . ' tiket melewati target')
                ->descriptionIcon('heroicon-m-clock')
                ->color($tickets['overdue'] ? 'danger' : 'success'),
            Stat::make('IKM', $survey['ikm'] !== null ? number_format($survey['ikm'], 2, ',', '.') : '–')
                ->description($survey['ikm_grade'] ?? 'Belum ada data survei')
                ->descriptionIcon('heroicon-m-face-smile')
                ->color('success'),
        ];
    }
}
