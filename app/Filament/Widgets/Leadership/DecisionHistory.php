<?php

namespace App\Filament\Widgets\Leadership;

use App\Filament\Pages\Leadership\DispositionHistory;
use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/** The leader's own recent decisions (Modul 8). */
class DecisionHistory extends TableWidget
{
    protected static ?string $heading = 'Keputusan terakhir Anda';

    protected int | string | array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Ticket::query()
                ->with(['service:id,name', 'user:id,name'])
                ->where('approved_by', auth()->id())
                ->whereNotNull('approved_at'))
            ->defaultSort('approved_at', 'desc')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')->label('No. Tiket'),
                Tables\Columns\TextColumn::make('service.name')->label('Layanan')->wrap(),
                Tables\Columns\TextColumn::make('user.name')->label('Pemohon'),
                Tables\Columns\TextColumn::make('approval_status')->label('Keputusan')->badge()
                    ->formatStateUsing(fn (?string $state) => $state === 'approved' ? 'Didisposisi' : 'Ditolak')
                    ->color(fn (?string $state) => $state === 'approved' ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('approved_at')->label('Tanggal')->dateTime('d M Y H:i'),
            ])
            ->recordUrl(fn (Ticket $record) => TicketResource::getUrl('view', ['record' => $record]))
            ->headerActions([
                Tables\Actions\Action::make('all')->label('Lihat semua riwayat')->link()
                    ->url(DispositionHistory::getUrl()),
            ])
            ->emptyStateHeading('Belum ada keputusan');
    }
}
