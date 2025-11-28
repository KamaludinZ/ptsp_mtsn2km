<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Ticket;
use App\Models\Service;
use Carbon\Carbon;

class TicketsByStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Tiket (14 Hari Terakhir)';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Ticket::select(
                \DB::raw('DATE(created_at) as date'),
                \DB::raw('count(case when status = \'pending\' then 1 end) as pending'),
                \DB::raw('count(case when status = \'processing\' then 1 end) as processing'),
                \DB::raw('count(case when status = \'completed\' then 1 end) as completed')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(13))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $labels = $data->map(fn ($item) => Carbon::parse($item->date)->format('d M'));

        return [
            'datasets' => [
                [
                    'label' => 'Tiket Pending',
                    'data' => $data->map(fn ($item) => $item->pending)->toArray(),
                    'borderColor' => '#f59e0b', // Amber-500
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Tiket Diproses',
                    'data' => $data->map(fn ($item) => $item->processing)->toArray(),
                    'borderColor' => '#3b82f6', // Blue-500
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Tiket Selesai',
                    'data' => $data->map(fn ($item) => $item->completed)->toArray(),
                    'borderColor' => '#16a34a', // Green-600
                    'backgroundColor' => 'rgba(22, 163, 74, 0.2)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Jumlah Tiket'
                    ]
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Hari'
                    ]
                ]
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }

    protected function getTitle(): string
    {
        return 'Performa Mingguan - Status Tiket';
    }
}