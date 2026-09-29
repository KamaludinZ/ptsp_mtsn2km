<?php

namespace App\Filament\Widgets\Performance;

use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/** Open tickets past their service-standard target date. */
class OverdueTickets extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'Melewati target waktu';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Ticket::query()->with(['service:id,name', 'user:id,name', 'assignedTo:id,name'])->overdue())
            ->defaultSort('estimated_completion_date')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')->label('No. Tiket')->weight('semibold'),
                Tables\Columns\TextColumn::make('service.name')->label('Layanan')->wrap(),
                Tables\Columns\TextColumn::make('user.name')->label('Pemohon'),
                Tables\Columns\TextColumn::make('assignedTo.name')->label('Petugas')->placeholder('Belum ditugaskan'),
                Tables\Columns\TextColumn::make('estimated_completion_date')->label('Target')->date('d M Y')->color('danger')
                    ->description(fn (Ticket $record) => $record->estimated_completion_date?->diffForHumans()),
            ])
            ->recordUrl(fn (Ticket $record) => TicketResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('Tidak ada tiket yang terlambat')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
