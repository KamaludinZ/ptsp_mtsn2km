<?php

namespace App\Filament\Widgets\Performance;

use App\Support\ServiceMetrics;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Service workload, timeliness, satisfaction and complaints for one period (Modul 13). */
class PerformanceStats extends StatsOverviewWidget
{
    use InteractsWithPageFilters;
    use ReadsPeriod;

    protected static bool $isDiscovered = false;

    protected static ?string $pollingInterval = null;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $from = $this->periodStart();
        $tickets = ServiceMetrics::tickets($from);
        $survey = ServiceMetrics::survey($from);
        $complaints = ServiceMetrics::complaints($from);
        $percent = fn (?int $value) => $value === null ? '–' : $value . '%';

        return [
            Stat::make('Permohonan', number_format($tickets['total'], 0, ',', '.'))
                ->description($tickets['online'] . ' online · ' . $tickets['offline'] . ' loket')
                ->descriptionIcon('heroicon-m-inbox-stack')
                ->color('primary'),
            Stat::make('Tingkat penyelesaian', $percent($tickets['completion_rate']))
                ->description($tickets['completed'] . ' selesai · ' . $tickets['open'] . ' masih diproses')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            Stat::make('Tepat waktu', $percent($tickets['on_time_rate']))
                ->description('Rata-rata ' . ServiceMetrics::days($tickets['avg_days']))
                ->descriptionIcon('heroicon-m-clock')
                ->color(($tickets['on_time_rate'] ?? 100) >= 80 ? 'success' : 'warning'),
            Stat::make('Melewati target', $tickets['overdue'])
                ->description($tickets['awaiting_approval'] . ' menunggu persetujuan pimpinan')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($tickets['overdue'] ? 'danger' : 'gray'),
            Stat::make('IKM (SKM)', $survey['ikm'] !== null ? number_format($survey['ikm'], 2, ',', '.') : '–')
                ->description($survey['ikm_grade'] ?? 'Belum ada data survei')
                ->descriptionIcon('heroicon-m-face-smile')
                ->color('success'),
            Stat::make('IPAK (SPAK)', $survey['ipak'] !== null ? number_format($survey['ipak'], 2, ',', '.') : '–')
                ->description($survey['ipak_grade'] ?? 'Belum ada data survei')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('info'),
            Stat::make('Pengaduan tertangani', $percent($complaints['dumas']['resolution_rate']))
                ->description($complaints['dumas']['total'] . ' pengaduan · ' . $complaints['whistleblowing']['total'] . ' whistleblowing')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('warning'),
            Stat::make('Responden survei', $survey['respondents'])
                ->description('Survei SKM & SPAK periode ini')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('gray'),
        ];
    }
}
