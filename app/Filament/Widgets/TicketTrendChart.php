<?php

namespace App\Filament\Widgets;

use App\Support\ServiceMetrics;
use Filament\Widgets\ChartWidget;

/**
 * Tren permohonan masuk vs selesai, shown on every staff role's dashboard.
 * The range filter switches between daily and monthly buckets.
 */
class TicketTrendChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 1;

    protected static ?string $heading = 'Tren permohonan';

    protected static ?string $pollingInterval = null;

    protected static ?string $maxHeight = '280px';

    protected int | string | array $columnSpan = 'full';

    public ?string $filter = '30d';

    public static function canView(): bool
    {
        return auth()->check();
    }

    protected function getFilters(): ?array
    {
        return ServiceMetrics::TREND_RANGES;
    }

    public function getDescription(): ?string
    {
        $data = $this->getCachedData();
        $empty = collect($data['datasets'])->every(fn (array $set) => array_sum($set['data']) === 0);

        return ($empty ? 'Belum ada permohonan pada rentang ini. ' : '') . 'Diperbarui pukul ' . now()->format('H:i') . '.';
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $trend = ServiceMetrics::trend($this->filter ?? '30d');

        return [
            'datasets' => [
                [
                    'label' => 'Masuk',
                    'data' => array_column($trend, 'masuk'),
                    'borderColor' => '#0ea5e9',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Selesai',
                    'data' => array_column($trend, 'selesai'),
                    'borderColor' => '#16a34a',
                    'backgroundColor' => 'rgba(22, 163, 74, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => array_column($trend, 'label'),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['position' => 'bottom']],
            'interaction' => ['mode' => 'index', 'intersect' => false],
            'scales' => [
                // Keep labels flat and sparse so the chart stays readable on phones.
                'x' => ['ticks' => ['autoSkip' => true, 'maxTicksLimit' => 8, 'maxRotation' => 0]],
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
        ];
    }
}
