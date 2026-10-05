<?php

namespace App\Filament\Pages\Reports;

use App\Support\MonthlyReport as Report;
use Filament\Pages\Page;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

/**
 * Laporan Bulanan / Kinerja Layanan: one month at a glance, compared with
 * the month before, for leaders, supervisors and the back office.
 */
class MonthlyReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $title = 'Laporan Bulanan';

    protected static ?string $navigationLabel = 'Laporan Bulanan';

    protected static ?string $slug = 'laporan-bulanan';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.monthly-report';

    #[Url(as: 'bulan')]
    public ?string $month = null;

    public static function canAccess(): bool
    {
        return Performance::canAccess();
    }

    public static function getNavigationGroup(): ?string
    {
        return Performance::getNavigationGroup();
    }

    /** Month (1-12) and year of the picker, kept in step with $month. */
    public int $monthNumber;

    public int $year;

    public function mount(): void
    {
        $this->go(Report::month($this->month));
    }

    public function updatedMonth(): void
    {
        $this->go(Report::month($this->month));
    }

    public function updatedMonthNumber(): void
    {
        $this->pick();
    }

    public function updatedYear(): void
    {
        $this->pick();
    }

    public function previousMonth(): void
    {
        $this->go(Report::month($this->month)->subMonthNoOverflow());
    }

    public function nextMonth(): void
    {
        $this->go(Report::month($this->month)->addMonthNoOverflow());
    }

    public function openMonth(string $month): void
    {
        $this->go(Report::month($month));
    }

    public function thisMonth(): void
    {
        $this->go(now()->startOfMonth());
    }

    public function canGoNext(): bool
    {
        return Report::month($this->month)->lt(now()->startOfMonth());
    }

    public function canGoPrevious(): bool
    {
        return Report::month($this->month)->year > min(Report::years());
    }

    /** @return array<int, array{name: string, disabled: bool}> months of the chosen year; future ones disabled */
    public function monthChoices(): array
    {
        return collect(Report::MONTH_NAMES)->map(fn (string $name, int $number) => [
            'name' => $name,
            'disabled' => $this->year === now()->year && $number > now()->month,
        ])->all();
    }

    public function yearChoices(): array
    {
        return Report::years();
    }

    /** Month + year from the two selects; a future month falls back to the latest allowed one. */
    private function pick(): void
    {
        $year = in_array($this->year, Report::years(), true) ? $this->year : now()->year;
        $number = min(max($this->monthNumber, 1), 12);
        if ($year === now()->year) {
            $number = min($number, now()->month);
        }

        $this->go(\Illuminate\Support\Carbon::create($year, $number, 1));
    }

    private function go(\Illuminate\Support\Carbon $month): void
    {
        $month = Report::month($month->format('Y-m'));
        $this->month = $month->format('Y-m');
        $this->monthNumber = $month->month;
        $this->year = $month->year;
        unset($this->report);
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('print')
                ->label('Cetak laporan')
                ->icon('heroicon-m-printer')
                ->color('gray')
                ->url(fn () => route('reports.monthly.print', ['bulan' => $this->month]))
                ->openUrlInNewTab(),
            \Filament\Actions\ActionGroup::make([
                \Filament\Actions\Action::make('downloadPdf')
                    ->label('PDF')
                    ->icon('heroicon-m-document-arrow-down')
                    ->url(fn () => route('reports.monthly.download', ['format' => 'pdf', 'bulan' => $this->month])),
                \Filament\Actions\Action::make('downloadXlsx')
                    ->label('Excel (.xlsx)')
                    ->icon('heroicon-m-table-cells')
                    ->url(fn () => route('reports.monthly.download', ['format' => 'xlsx', 'bulan' => $this->month])),
            ])
                ->label('Unduh')
                ->icon('heroicon-m-arrow-down-tray')
                ->button(),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Kinerja layanan PTSP per bulan: jumlah permohonan, penyelesaian, ketepatan waktu, kepuasan, dan pengaduan.';
    }

    #[Computed]
    public function report(): array
    {
        return Report::build(Report::month($this->month));
    }

}
