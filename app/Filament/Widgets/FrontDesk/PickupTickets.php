<?php

namespace App\Filament\Widgets\FrontDesk;

use App\Filament\Concerns\NotifiesActionResult;
use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use App\Services\TicketService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/** Finished products waiting at the counter (Modul 9). */
class PickupTickets extends TableWidget
{
    use NotifiesActionResult;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 11;

    protected static ?string $heading = 'Siap diambil di loket';

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasRole('front_desk');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Ticket::query()
                ->with(['user:id,name,whatsapp_number', 'service:id,name'])
                ->where('status', 'completed')
                ->where('ready_for_pickup', true))
            ->defaultSort('actual_completion_date', 'desc')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')->label('No. Tiket')->weight('semibold')->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('Pemohon')->searchable()
                    ->description(fn (Ticket $record) => $record->user?->whatsapp_number),
                Tables\Columns\TextColumn::make('service.name')->label('Layanan')->wrap(),
                Tables\Columns\TextColumn::make('actual_completion_date')->label('Selesai')->date('d M Y'),
            ])
            ->actions([
                Tables\Actions\Action::make('handOver')
                    ->label('Serahkan')
                    ->icon('heroicon-m-hand-raised')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Pastikan produk layanan sudah diterima pemohon.')
                    ->action(fn (Ticket $record) => self::attempt(
                        fn () => app(TicketService::class)->handOver($record, auth()->user()),
                        "Produk layanan tiket {$record->ticket_number} sudah diserahkan.",
                    )),
            ])
            ->recordUrl(fn (Ticket $record) => TicketResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('Tidak ada produk yang menunggu diambil')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
