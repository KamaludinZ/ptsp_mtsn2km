<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Reports\Performance;
use App\Models\Ticket;
use App\Support\ServiceMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Cuplikan laporan bulan berjalan: the headline figures of this month's
 * performance report, linking to the full report for roles that may open it.
 */
class MonthlyReportSnapshot extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 2;

    protected static ?string $pollingInterval = null;

    public static function canView(): bool
    {
        return auth()->check();
    }

    protected function getHeading(): ?string
    {
        return 'Laporan Bulan ' . now()->translatedFormat('F Y');
    }

    protected function getDescription(): ?string
    {
        $empty = ! Ticket::where('created_at', '>=', now()->startOfMonth())->exists();

        return ($empty ? 'Belum ada permohonan bulan ini.' : 'Berjalan sejak ' . now()->startOfMonth()->translatedFormat('j F') . ' sampai hari ini.')
            . ' Diperbarui pukul ' . now()->format('H:i') . '.';
    }

    protected function getStats(): array
    {
        $from = now()->startOfMonth();
        $tickets = ServiceMetrics::tickets($from);
        $survey = ServiceMetrics::survey($from);
        $report = Performance::canAccess() ? Performance::getUrl() : null;
        $percent = fn (?int $value) => $value !== null ? $value . '%' : '–';

        return [
            Stat::make('Permohonan', number_format($tickets['total'], 0, ',', '.'))
                ->description(number_format($tickets['completed'], 0, ',', '.') . ' selesai · ' . $percent($tickets['completion_rate']) . ' penyelesaian')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary')
                ->url($report),
            Stat::make('Tepat waktu', $percent($tickets['on_time_rate']))
                ->description('Selesai sesuai standar layanan')
                ->descriptionIcon('heroicon-m-clock')
                ->color(match (true) {
                    $tickets['on_time_rate'] === null => 'gray',
                    $tickets['on_time_rate'] >= 80 => 'success',
                    default => 'warning',
                }),
            Stat::make('Rata-rata penyelesaian', ServiceMetrics::days($tickets['avg_days']))
                ->description('Sejak permohonan masuk')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
            Stat::make('IKM (SKM)', $survey['ikm'] !== null ? number_format($survey['ikm'], 2, ',', '.') : '–')
                ->description($survey['ikm_grade'] ?? number_format($survey['respondents'], 0, ',', '.') . ' responden')
                ->descriptionIcon('heroicon-m-face-smile')
                ->color($survey['ikm'] !== null ? 'success' : 'gray'),
        ];
    }
}
