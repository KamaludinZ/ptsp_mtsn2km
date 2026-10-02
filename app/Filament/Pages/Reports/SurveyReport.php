<?php

namespace App\Filament\Pages\Reports;

use App\Exports\SurveyResponsesExport;
use App\Models\SurveyEdition;
use App\Support\RoleAccess;
use App\Support\ServiceMetrics;
use App\Support\SurveyReports;
use Carbon\CarbonInterface;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;
use Maatwebsite\Excel\Facades\Excel;

/**
 * SKM (IKM) and SPAK (IPAK) results for a chosen period and survey edition
 * (the current month by default), following Permenpan RB 14/2017, with
 * respondent demographics and the quarterly archives. Printable and
 * downloadable as Excel.
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

    public const PERIODS = [
        'month' => 'Bulan ini',
        'last_month' => 'Bulan lalu',
        'quarter' => 'Triwulan ini',
        'year' => 'Tahun ini',
        'all' => 'Semua data',
    ];

    #[Url(as: 'survei')]
    public string $type = 'skm';

    #[Url(as: 'periode')]
    public string $period = 'month';

    #[Url(as: 'edisi')]
    public ?string $edition = null;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasAnyRole(RoleAccess::COMPLAINT_HANDLERS);
    }

    /** @return array{0: CarbonInterface, 1: CarbonInterface} */
    public function range(): array
    {
        return match ($this->period) {
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'quarter' => [now()->startOfQuarter(), now()->endOfQuarter()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            'all' => [Carbon::create(2000), now()->endOfDay()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    public function periodLabel(): string
    {
        [$from, $to] = $this->range();

        return match ($this->period) {
            'all' => 'seluruh periode',
            'month', 'last_month' => 'bulan ' . $from->translatedFormat('F Y'),
            default => $from->translatedFormat('j M Y') . ' – ' . $to->translatedFormat('j M Y'),
        };
    }

    public function getSubheading(): ?string
    {
        $edition = $this->edition ? SurveyEdition::find($this->edition)?->name : null;

        return 'Hasil survei ' . $this->periodLabel() . ($edition ? ', edisi ' . $edition : '') . ', dan arsip per triwulan.';
    }

    public function updatedPeriod(): void
    {
        if (! array_key_exists($this->period, self::PERIODS)) {
            $this->period = 'month';
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('Cetak')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->alpineClickHandler('window.print()'),
            Action::make('excel')
                ->label('Unduh Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    [$from, $to] = $this->range();

                    return Excel::download(
                        new SurveyResponsesExport($from->toDateString(), $to->toDateString(), $this->edition ?: null),
                        'laporan-survei-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.xlsx',
                    );
                }),
        ];
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
        [$from, $to] = $this->range();
        $reports = new SurveyReports($from, $to, $this->edition ? (int) $this->edition : null);
        $report = $type === 'skm' ? $reports->skm() : $reports->spak();
        $index = $report['results']['average'] ? round($report['results']['average'] * 25, 2) : null;

        return [
            'surveyType' => $type,
            'periods' => self::PERIODS,
            'editions' => SurveyEdition::orderByDesc('year')->orderByDesc('id')->pluck('name', 'id'),
            'periodLabel' => $this->periodLabel(),
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
