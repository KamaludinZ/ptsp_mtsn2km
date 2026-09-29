<?php

namespace App\Filament\Widgets\Supervision;

use App\Filament\Resources\ComplaintResource;
use App\Support\ServiceMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SupervisionStats extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 30;

    protected static ?string $pollingInterval = null;

    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasRole('supervisor');
    }

    protected function getStats(): array
    {
        $survey = ServiceMetrics::survey(now()->startOfYear());
        $complaints = ServiceMetrics::complaints(now()->startOfYear());
        $index = fn (?float $value) => $value !== null ? number_format($value, 2, ',', '.') : '–';

        return [
            Stat::make('IKM tahun ini', $index($survey['ikm']))
                ->description($survey['ikm_grade'] ?? 'Belum ada data survei')
                ->descriptionIcon('heroicon-m-face-smile')
                ->color('success'),
            Stat::make('IPAK tahun ini', $index($survey['ipak']))
                ->description($survey['respondents'] . ' responden')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('info'),
            Stat::make('Pengaduan baru', $complaints['dumas']['new'])
                ->description($complaints['dumas']['in_progress'] . ' sedang ditindaklanjuti')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color($complaints['dumas']['new'] ? 'warning' : 'gray')
                ->url(ComplaintResource::getUrl('index')),
            Stat::make('Whistleblowing baru', $complaints['whistleblowing']['new'])
                ->description($complaints['whistleblowing']['in_progress'] . ' sedang ditindaklanjuti')
                ->descriptionIcon('heroicon-m-shield-exclamation')
                ->color($complaints['whistleblowing']['new'] ? 'danger' : 'gray')
                ->url(ComplaintResource::getUrl('index', ['activeTab' => 'whistleblowing'])),
        ];
    }
}
