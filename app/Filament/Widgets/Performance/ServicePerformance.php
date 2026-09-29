<?php

namespace App\Filament\Widgets\Performance;

use App\Models\Service;
use App\Models\Ticket;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/** Workload and timeliness per service, busiest first. */
class ServicePerformance extends TableWidget
{
    use InteractsWithPageFilters;
    use ReadsPeriod;

    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'Kinerja per layanan';

    protected int | string | array $columnSpan = ['default' => 'full', 'xl' => 2];

    public function table(Table $table): Table
    {
        $from = $this->periodStart();
        $inPeriod = fn ($q) => $q->when($from, fn ($q) => $q->where('created_at', '>=', $from));

        return $table
            ->query(fn () => Service::query()
                ->whereHas('tickets', $inPeriod)
                ->withCount([
                    'tickets as total' => $inPeriod,
                    'tickets as completed' => fn ($q) => $inPeriod($q)->where('status', 'completed'),
                    'tickets as overdue' => fn ($q) => $inPeriod($q)->overdue(),
                ])
                ->addSelect(['avg_days' => Ticket::query()
                    ->selectRaw('round(avg(actual_completion_date - created_at::date), 1)')
                    ->whereColumn('service_id', 'services.id')
                    ->where('status', 'completed')
                    ->when($from, fn ($q) => $q->where('created_at', '>=', $from))]))
            ->defaultSort('total', 'desc')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(10)
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Layanan')->wrap(),
                Tables\Columns\TextColumn::make('processing_time')->label('Standar waktu')->placeholder('–')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total')->label('Permohonan')->numeric()->sortable()->alignEnd(),
                Tables\Columns\TextColumn::make('completed')->label('Selesai')->numeric()->sortable()->alignEnd(),
                Tables\Columns\TextColumn::make('overdue')->label('Terlambat')->numeric()->sortable()->alignEnd()
                    ->color(fn ($state) => $state ? 'danger' : null),
                Tables\Columns\TextColumn::make('avg_days')->label('Rata-rata')->alignEnd()
                    ->formatStateUsing(fn ($state) => \App\Support\ServiceMetrics::days($state))
                    ->placeholder('–'),
            ])
            ->emptyStateHeading('Belum ada permohonan pada periode ini');
    }
}
