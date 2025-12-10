<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Complaint;
use Illuminate\Support\Carbon;
use Filament\Forms\Components\Select;

class ComplaintsChart extends ChartWidget
{
    protected ?string $heading = 'Statistik Pengaduan';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected ?string $maxHeight = '300px';
    protected static bool $isLazy = true;

    public ?string $filter = 'semester'; // Default filter

    protected function getData(): array
    {
        $dataMasuk = [];
        $dataPending = [];
        $dataSelesai = [];
        $labels = [];

        // Check if filter is a specific year (numeric)
        if (is_numeric($this->filter)) {
            // Show 12 months of that specific year
            $year = (int) $this->filter;

            for ($month = 1; $month <= 12; $month++) {
                $date = Carbon::create($year, $month, 1);

                // Total pengaduan masuk (hanya complaint dan suggestion, bukan whistleblowing)
                $totalMasuk = Complaint::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->whereIn('complaint_type', ['complaint', 'suggestion'])
                    ->count();

                // Pengaduan pending
                $totalPending = Complaint::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->whereIn('complaint_type', ['complaint', 'suggestion'])
                    ->whereIn('status', ['submitted', 'in_review', 'in_progress'])
                    ->count();

                // Pengaduan selesai
                $totalSelesai = Complaint::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->whereIn('complaint_type', ['complaint', 'suggestion'])
                    ->whereIn('status', ['resolved', 'closed'])
                    ->count();

                $dataMasuk[] = $totalMasuk;
                $dataPending[] = $totalPending;
                $dataSelesai[] = $totalSelesai;
                $labels[] = $date->translatedFormat('M'); // Short month name
            }
        } else {
            // Show last N months (rolling)
            $monthsToShow = $this->filter === 'year' ? 12 : 6;

            for ($i = $monthsToShow - 1; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);

                // Total pengaduan masuk (hanya complaint dan suggestion, bukan whistleblowing)
                $totalMasuk = Complaint::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->whereIn('complaint_type', ['complaint', 'suggestion'])
                    ->count();

                // Pengaduan pending (submitted, in_review, in_progress)
                $totalPending = Complaint::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->whereIn('complaint_type', ['complaint', 'suggestion'])
                    ->whereIn('status', ['submitted', 'in_review', 'in_progress'])
                    ->count();

                // Pengaduan selesai (resolved, closed)
                $totalSelesai = Complaint::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->whereIn('complaint_type', ['complaint', 'suggestion'])
                    ->whereIn('status', ['resolved', 'closed'])
                    ->count();

                $dataMasuk[] = $totalMasuk;
                $dataPending[] = $totalPending;
                $dataSelesai[] = $totalSelesai;
                $labels[] = $date->translatedFormat('M Y'); // Short format with year
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pengaduan Masuk',
                    'data' => $dataMasuk,
                    'borderColor' => '#3B82F6', // Blue
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Pending',
                    'data' => $dataPending,
                    'borderColor' => '#F59E0B', // Orange
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Selesai',
                    'data' => $dataSelesai,
                    'borderColor' => '#10B981', // Green
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'tension' => 0.3,
                ],
            ],
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

        // Get the earliest complaint to determine start year (hanya complaint dan suggestion)
        $earliestComplaint = Complaint::whereIn('complaint_type', ['complaint', 'suggestion'])
            ->orderBy('created_at', 'asc')
            ->first();

        $currentYear = Carbon::now()->year;

        // If there are complaints, get the year from the earliest one
        // Otherwise, just use current year
        $startYear = $earliestComplaint
            ? Carbon::parse($earliestComplaint->created_at)->year
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
