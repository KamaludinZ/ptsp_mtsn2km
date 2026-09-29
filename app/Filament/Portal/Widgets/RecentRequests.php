<?php

namespace App\Filament\Portal\Widgets;

use App\Filament\Portal\Resources\TicketResource;
use App\Models\Ticket;
use App\Support\TicketLabels;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentRequests extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'Permohonan terbaru';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Ticket::query()->where('user_id', auth()->id())->with('service:id,name'))
            ->defaultSort('created_at', 'desc')
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')->label('No. Tiket')->weight('semibold'),
                Tables\Columns\TextColumn::make('service.name')->label('Layanan')->wrap(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (?string $state) => TicketLabels::status($state))
                    ->color(fn (?string $state) => TicketLabels::statusColor($state)),
                Tables\Columns\TextColumn::make('created_at')->label('Diajukan')->date('d M Y'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('apply')->label('Ajukan layanan')->icon('heroicon-m-plus')->url(route('onlineportal.service.catalog')),
            ])
            ->recordUrl(fn (Ticket $record) => TicketResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('Belum ada permohonan')
            ->emptyStateDescription('Pilih layanan di katalog untuk mengajukan permohonan pertama Anda.');
    }
}
