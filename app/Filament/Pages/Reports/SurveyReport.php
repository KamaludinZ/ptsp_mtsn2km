<?php

namespace App\Filament\Pages\Reports;

use App\Support\RoleAccess;
use App\Support\ServiceMetrics;
use App\Support\SurveyReports;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

/**
 * SKM (IKM) and SPAK (IPAK) results for the current month, following
 * Permenpan RB 14/2017, with respondent demographics and the quarterly
 * archives.
 */
class SurveyReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationGroup = 'Pengawasan';

    protected static ?string $navigationLabel = 'Laporan SKM & SPAK';

    protected static ?string $title = 'Laporan SKM & SPAK';

    protected static ?string $slug = 'laporan-survei';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.survey-report';

    #[Url(as: 'survei')]
    public string $type = 'skm';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasAnyRole(RoleAccess::COMPLAINT_HANDLERS);
    }

    public function getSubheading(): ?string
    {
        return 'Hasil survei bulan ' . now()->translatedFormat('F Y') . ' dan arsip per triwulan.';
    }

    public function updatedType(): void
    {
        if (! in_array($this->type, ['skm', 'spak'], true)) {
            $this->type = 'skm';
        }
    }

    protected function getViewData(): array
    {
        $type = in_array($this->type, ['skm', 'spak'], true) ? $this->type : 'skm';
        $reports = new SurveyReports();
        $report = $type === 'skm' ? $reports->skm() : $reports->spak();
        $index = $report['results']['average'] ? round($report['results']['average'] * 25, 2) : null;

        return [
            'surveyType' => $type,
            'report' => $report,
            'index' => $index,
            'grade' => ServiceMetrics::grade($index),
            'archives' => $reports->archives($type),
            'demographicLabels' => [
                'age_groups' => 'Usia',
                'education_levels' => 'Pendidikan',
                'job_types' => 'Pekerjaan',
                'service_types' => 'Jenis layanan',
            ],
        ];
    }
}
