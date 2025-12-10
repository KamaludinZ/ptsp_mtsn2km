<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Visitor;
use Illuminate\Support\Carbon;

class VisitorsChart extends ChartWidget
{
    protected ?string $heading = 'Statistik Pengunjung Berdasarkan Jenis Instansi';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    protected ?string $maxHeight = '300px';
    protected static bool $isLazy = true;

    public ?string $filter = 'semester'; // Default filter

    protected function getData(): array
    {
        $categories = [
            'Pemerintah' => [],
            'Swasta' => [],
            'Pendidikan' => [],
            'Pribadi' => [],
            'Lainnya' => [],
        ];
        $labels = [];

        // Check if filter is a specific year (numeric)
        if (is_numeric($this->filter)) {
            // Show 12 months of that specific year
            $year = (int) $this->filter;

            for ($month = 1; $month <= 12; $month++) {
                $date = Carbon::create($year, $month, 1);
                $labels[] = $date->translatedFormat('M'); // Short month name

                foreach (array_keys($categories) as $category) {
                    $count = Visitor::whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->where('institution_category', $category)
                        ->count();
                    $categories[$category][] = $count;
                }
            }
        } else {
            // Show last N months (rolling)
            $monthsToShow = $this->filter === 'year' ? 12 : 6;

            for ($i = $monthsToShow - 1; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $labels[] = $date->translatedFormat('M Y'); // Short format with year

                foreach (array_keys($categories) as $category) {
                    $count = Visitor::whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->where('institution_category', $category)
                        ->count();
                    $categories[$category][] = $count;
                }
            }
        }

        // Color palette for different categories
        $colors = [
            'Pemerintah' => ['border' => '#3B82F6', 'bg' => 'rgba(59, 130, 246, 0.1)'],    // Blue
            'Swasta' => ['border' => '#8B5CF6', 'bg' => 'rgba(139, 92, 246, 0.1)'],         // Purple
            'Pendidikan' => ['border' => '#10B981', 'bg' => 'rgba(16, 185, 129, 0.1)'],    // Green
            'Pribadi' => ['border' => '#F59E0B', 'bg' => 'rgba(245, 158, 11, 0.1)'],       // Orange
            'Lainnya' => ['border' => '#6B7280', 'bg' => 'rgba(107, 114, 128, 0.1)'],      // Gray
        ];

        $datasets = [];
        foreach ($categories as $category => $data) {
            $datasets[] = [
                'label' => $category,
                'data' => $data,
                'borderColor' => $colors[$category]['border'],
                'backgroundColor' => $colors[$category]['bg'],
                'tension' => 0.3,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        $filters = [
            'semester' => '6 Bulan Terakhir',
            'year' => '12 Bulan Terakhir',
        ];

        // Get the earliest visitor to determine start year
        $earliestVisitor = Visitor::orderBy('created_at', 'asc')->first();

        $currentYear = Carbon::now()->year;

        // If there are visitors, get the year from the earliest one
        // Otherwise, just use current year
        $startYear = $earliestVisitor
            ? Carbon::parse($earliestVisitor->created_at)->year
            : $currentYear;

        // Generate year filters from earliest year to current year
        for ($y = $startYear; $y <= $currentYear; $y++) {
            $filters[(string) $y] = "Tahun {$y}";
        }

        return $filters;
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
