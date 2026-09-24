<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\SurveyResponse;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use Illuminate\Support\Carbon;

class SkmChart extends ChartWidget
{
    protected static ?string $heading = 'Statistik Indeks Survei Kepuasan Masyarakat (SKM)';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '300px';
    protected static bool $isLazy = true;

    public ?string $filter = 'semester'; // Default filter

    protected function getData(): array
    {
        $dataIndeks = [];
        $dataResponden = [];
        $labels = [];

        // Get SKM survey
        $skmSurvey = Survey::where('type', Survey::TYPE_SKM)->first();

        if (!$skmSurvey) {
            // If no SKM survey exists, return empty data
            return [
                'datasets' => [
                    [
                        'label' => 'Indeks SKM',
                        'data' => [],
                        'borderColor' => '#10B981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'tension' => 0.3,
                        'yAxisID' => 'y',
                    ],
                    [
                        'label' => 'Jumlah Responden',
                        'data' => [],
                        'borderColor' => '#3B82F6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'tension' => 0.3,
                        'yAxisID' => 'y1',
                    ],
                ],
                'labels' => [],
            ];
        }

        // Check if filter is a specific year (numeric)
        if (is_numeric($this->filter)) {
            // Show 12 months of that specific year
            $year = (int) $this->filter;

            for ($month = 1; $month <= 12; $month++) {
                $date = Carbon::create($year, $month, 1);
                $labels[] = $date->translatedFormat('M'); // Short month name

                // Get responses for this month
                $responses = SurveyResponse::where('survey_id', $skmSurvey->id)
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->whereNotNull('completed_at')
                    ->get();

                $totalResponden = $responses->count();
                $dataResponden[] = $totalResponden;

                if ($totalResponden > 0) {
                    // Calculate average score
                    $totalScore = 0;
                    $answerCount = 0;

                    foreach ($responses as $response) {
                        $answers = SurveyAnswer::where('survey_response_id', $response->id)->get();
                        foreach ($answers as $answer) {
                            if (is_numeric($answer->answer_value)) {
                                $totalScore += (float) $answer->answer_value;
                                $answerCount++;
                            }
                        }
                    }

                    $avgScore = $answerCount > 0 ? ($totalScore / $answerCount) : 0;
                    // Convert to 100 scale (assuming answer_value is 1-5)
                    $indeks = ($avgScore / 5) * 100;
                    $dataIndeks[] = round($indeks, 2);
                } else {
                    $dataIndeks[] = 0;
                }
            }
        } else {
            // Show last N months (rolling)
            $monthsToShow = $this->filter === 'year' ? 12 : 6;

            for ($i = $monthsToShow - 1; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $labels[] = $date->translatedFormat('M Y'); // Short format with year

                // Get responses for this month
                $responses = SurveyResponse::where('survey_id', $skmSurvey->id)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->whereNotNull('completed_at')
                    ->get();

                $totalResponden = $responses->count();
                $dataResponden[] = $totalResponden;

                if ($totalResponden > 0) {
                    // Calculate average score
                    $totalScore = 0;
                    $answerCount = 0;

                    foreach ($responses as $response) {
                        $answers = SurveyAnswer::where('survey_response_id', $response->id)->get();
                        foreach ($answers as $answer) {
                            if (is_numeric($answer->answer_value)) {
                                $totalScore += (float) $answer->answer_value;
                                $answerCount++;
                            }
                        }
                    }

                    $avgScore = $answerCount > 0 ? ($totalScore / $answerCount) : 0;
                    // Convert to 100 scale (assuming answer_value is 1-5)
                    $indeks = ($avgScore / 5) * 100;
                    $dataIndeks[] = round($indeks, 2);
                } else {
                    $dataIndeks[] = 0;
                }
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Indeks SKM (%)',
                    'data' => $dataIndeks,
                    'borderColor' => '#10B981', // Green
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'tension' => 0.3,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Jumlah Responden',
                    'data' => $dataResponden,
                    'borderColor' => '#3B82F6', // Blue
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.3,
                    'yAxisID' => 'y1',
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

        // Get the earliest response to determine start year
        $earliestResponse = SurveyResponse::orderBy('created_at', 'asc')->first();

        $currentYear = Carbon::now()->year;

        // If there are responses, get the year from the earliest one
        // Otherwise, just use current year
        $startYear = $earliestResponse
            ? Carbon::parse($earliestResponse->created_at)->year
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
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'beginAtZero' => true,
                    'max' => 100,
                    'title' => [
                        'display' => true,
                        'text' => 'Indeks SKM (%)',
                    ],
                    'ticks' => [
                        'stepSize' => 10,
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Jumlah Responden',
                    ],
                    'ticks' => [
                        'stepSize' => 1,
                        'precision' => 0,
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }
}
