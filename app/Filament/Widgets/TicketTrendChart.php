<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

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
        return [
            '7d' => '7 hari terakhir',
            '30d' => '30 hari terakhir',
            '12m' => '12 bulan terakhir',
        ];
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
        $monthly = $this->filter === '12m';
        $from = match ($this->filter) {
            '7d' => today()->subDays(6),
            '12m' => today()->startOfMonth()->subMonths(11),
            default => today()->subDays(29),
        };
        $unit = $monthly ? 'month' : 'day';
        $key = $monthly ? 'Y-m' : 'Y-m-d';

        $count = fn (string $column, ?string $status = null) => Ticket::query()
            ->where($column, '>=', $from)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->selectRaw("date_trunc('{$unit}', {$column})::date as bucket, count(*) as total")
            ->groupBy('bucket')
            ->pluck('total', 'bucket')
            ->mapWithKeys(fn ($total, $bucket) => [date($key, strtotime($bucket)) => (int) $total]);

        $in = $count('created_at');
        $done = $count('actual_completion_date', 'completed');

        $buckets = collect(CarbonPeriod::create($from, $monthly ? '1 month' : '1 day', today()));

        return [
            'datasets' => [
                [
                    'label' => 'Masuk',
                    'data' => $buckets->map(fn ($d) => $in->get($d->format($key), 0))->all(),
                    'borderColor' => '#0ea5e9',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Selesai',
                    'data' => $buckets->map(fn ($d) => $done->get($d->format($key), 0))->all(),
                    'borderColor' => '#16a34a',
                    'backgroundColor' => 'rgba(22, 163, 74, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $buckets->map(fn ($d) => $d->translatedFormat($monthly ? 'M Y' : 'j M'))->all(),
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
