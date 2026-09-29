<?php

namespace App\Filament\Widgets\BackOffice;

use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use App\Support\TicketLabels;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/** Open tickets, closest to breaching the service standard first (Modul 7). */
class TicketQueue extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 21;

    protected static ?string $heading = 'Antrian terdekat dengan target waktu';

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasRole('back_office');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Ticket::query()
                ->with(['user:id,name', 'service:id,name', 'assignedTo:id,name'])
                ->open()
                ->orderByRaw('estimated_completion_date asc nulls last')
                ->orderBy('created_at'))
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(10)
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')->label('No. Tiket')->weight('semibold')->searchable(),
                Tables\Columns\TextColumn::make('service.name')->label('Layanan')->wrap()->limit(40),
                Tables\Columns\TextColumn::make('user.name')->label('Pemohon'),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (?string $state) => TicketLabels::status($state))
                    ->color(fn (?string $state) => TicketLabels::statusColor($state)),
                Tables\Columns\TextColumn::make('assignedTo.name')->label('Petugas')->placeholder('Belum ditugaskan'),
                Tables\Columns\TextColumn::make('estimated_completion_date')->label('Target')->date('d M Y')->placeholder('–')
                    ->color(fn (Ticket $record) => $record->isOverdue() ? 'danger' : null),
            ])
            ->recordUrl(fn (Ticket $record) => TicketResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('Antrian kosong')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
