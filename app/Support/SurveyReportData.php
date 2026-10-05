<?php

namespace App\Support;

use App\Filament\Pages\Reports\SurveyReport;
use App\Models\SurveyEdition;

/**
 * One survey report (SKM → IKM or SPAK → IPAK) for a period and edition,
 * as shown on the report page, returned by the API and printed to PDF.
 */
class SurveyReportData
{
    public const DEMOGRAPHICS = [
        'age_groups' => 'Usia',
        'education_levels' => 'Pendidikan',
        'job_types' => 'Pekerjaan',
        'service_types' => 'Jenis layanan',
    ];

    public static function build(string $type, string $period = 'month', ?int $editionId = null): array
    {
        [$from, $to] = SurveyReport::rangeFor($period);
        $reports = new SurveyReports($from, $to, $editionId);
        $report = $type === 'spak' ? $reports->spak() : $reports->skm();
        $results = $report['results'];
        $index = $results['average'] ? round($results['average'] * 25, 2) : null;

        return [
            'type' => $type === 'spak' ? 'spak' : 'skm',
            'label' => $type === 'spak' ? 'IPAK' : 'IKM',
            'title' => $type === 'spak' ? 'Survei Persepsi Anti Korupsi (SPAK)' : 'Survei Kepuasan Masyarakat (SKM)',
            'from' => $from,
            'to' => $to,
            'edition' => $editionId ? SurveyEdition::find($editionId)?->name : null,
            'index' => $index,
            'grade' => ServiceMetrics::grade($index),
            'average' => $results['average'],
            'respondents' => $results['total_respondents'] ?? 0,
            'answers' => $report['responses_count'] ?? 0,
            'scores' => $results['scores'] ?? [],
            'demographics' => $report['demographics'] ?? [],
        ];
    }
}
