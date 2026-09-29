<?php

namespace App\Filament\Portal\Widgets;

use App\Filament\Portal\Resources\TicketResource;
use App\Models\Ticket;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/** Finished services: download the digital product or collect it at the counter (Modul 9). */
class ReadyResults extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'Hasil layanan';

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return static::results()->exists();
    }

    private static function results()
    {
        return Ticket::query()
            ->where('user_id', auth()->id())
            ->where('status', 'completed')
            ->where(fn ($q) => $q->has('output')->orWhere('ready_for_pickup', true));
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => static::results()->with(['service:id,name', 'output']))
            ->defaultSort('actual_completion_date', 'desc')
            ->paginated([5])
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')->label('No. Tiket')->weight('semibold'),
                Tables\Columns\TextColumn::make('service.name')->label('Layanan')->wrap(),
                Tables\Columns\TextColumn::make('actual_completion_date')->label('Selesai')->date('d M Y'),
                Tables\Columns\TextColumn::make('ready_for_pickup')->label('Keterangan')
                    ->state(fn (Ticket $record) => $record->output ? 'Siap diunduh' : 'Ambil di loket PTSP')
                    ->badge()
                    ->color(fn (Ticket $record) => $record->output ? 'success' : 'warning'),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Unduh')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->visible(fn (Ticket $record) => (bool) $record->output)
                    ->url(fn (Ticket $record) => route('documents.ticket-output', $record->output))
                    ->openUrlInNewTab(),
            ])
            ->recordUrl(fn (Ticket $record) => TicketResource::getUrl('view', ['record' => $record]));
    }
}
