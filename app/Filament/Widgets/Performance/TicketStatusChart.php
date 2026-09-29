<?php

namespace App\Filament\Widgets\Performance;

use App\Support\ServiceMetrics;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class TicketStatusChart extends ChartWidget
{
    use InteractsWithPageFilters;
    use ReadsPeriod;

    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'Permohonan per status';

    protected static ?string $pollingInterval = null;

    protected static ?string $maxHeight = '260px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $byStatus = ServiceMetrics::tickets($this->periodStart())['by_status'];

        return [
            'datasets' => [[
                'data' => array_values($byStatus),
                'backgroundColor' => ['#9ca3af', '#38bdf8', '#f59e0b', '#a3e635', '#16a34a', '#ef4444', '#6b7280'],
            ]],
            'labels' => array_values(array_intersect_key(ServiceMetrics::STATUS_LABELS, $byStatus)),
        ];
    }

    protected function getOptions(): array
    {
        return ['plugins' => ['legend' => ['position' => 'bottom']], 'scales' => ['x' => ['display' => false], 'y' => ['display' => false]]];
    }
}
