<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Widgets\Performance\OverdueTickets;
use App\Filament\Widgets\Performance\PerformanceStats;
use App\Filament\Widgets\Performance\ServicePerformance;
use App\Filament\Widgets\Performance\TicketStatusChart;
use App\Support\RoleAccess;
use App\Support\ServiceMetrics;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

/**
 * Service performance (Modul 13) for leaders and supervisors: workload,
 * timeliness against the service standards, satisfaction and complaints.
 */
class Performance extends BaseDashboard
{
    use HasFiltersForm;

    protected static string $routePath = 'kinerja';

    protected static ?string $title = 'Kinerja Pelayanan';

    protected static ?string $navigationLabel = 'Kinerja Pelayanan';

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) $user && ($user->hasAnyRole(RoleAccess::LEADERSHIP)
            || $user->can('supervision.access')
            || $user->can('backoffice.access'));
    }

    public static function getNavigationGroup(): ?string
    {
        $user = auth()->user();

        return match (true) {
            (bool) $user?->hasAnyRole(RoleAccess::LEADERSHIP) => 'Pimpinan',
            (bool) $user?->can('supervision.access') => 'Pengawasan',
            default => 'Back Office',
        };
    }

    public function getSubheading(): ?string
    {
        return 'Beban kerja, ketepatan waktu terhadap standar pelayanan, kepuasan masyarakat, dan pengaduan.';
    }

    public function filtersForm(Form $form): Form
    {
        return $form->schema([
            Select::make('periode')
                ->label('Periode')
                ->options(ServiceMetrics::PERIODS)
                ->default('year')
                ->selectablePlaceholder(false),
        ]);
    }

    public function getWidgets(): array
    {
        return [
            PerformanceStats::class,
            ServicePerformance::class,
            TicketStatusChart::class,
            OverdueTickets::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return ['default' => 1, 'xl' => 3];
    }
}
