<?php

namespace App\Filament\Resources\SuratKeputusanResource\Widgets;

use App\Services\SuratKeputusanService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

/** Above the SK register: this year's numbers, the last one given and the next one in line. */
class SkYearOverview extends StatsOverviewWidget
{
    protected static ?string $pollingInterval = null;

    /** Cheap counts that matter before taking a number: render with the page. */
    protected static bool $isLazy = false;

    /** Re-rendered after a number is taken on the page. */
    #[On('sk-numbers-changed')]
    public function refreshNumbers(): void
    {
    }

    protected function getStats(): array
    {
        $summary = SuratKeputusanService::yearSummary(now()->year);

        return [
            Stat::make('Nomor SK terbit ' . $summary['tahun'], $summary['terbit'])
                ->description('Berurutan 1–' . SuratKeputusanService::MAX_NUMBER . ', mulai ulang tiap tahun')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('primary'),
            Stat::make('Nomor terakhir', $summary['terakhir'] ?? '–')
                ->description($summary['terakhir'] ? 'Nomor SK terbaru tahun ini' : 'Belum ada nomor tahun ini')
                ->descriptionIcon('heroicon-m-clock'),
            Stat::make('Nomor berikutnya', $summary['berikutnya'] ?? 'Habis')
                ->description($summary['berikutnya'] ? 'Diberikan pada permintaan berikutnya' : 'Nomor tahun ini sudah mencapai ' . SuratKeputusanService::MAX_NUMBER)
                ->descriptionIcon('heroicon-m-hashtag')
                ->color($summary['berikutnya'] ? 'success' : 'danger'),
            Stat::make('Menunggu scan', $summary['menunggu_scan'])
                ->description('SK tahun ini yang belum diunggah scannya')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color($summary['menunggu_scan'] ? 'warning' : 'gray'),
        ];
    }
}
