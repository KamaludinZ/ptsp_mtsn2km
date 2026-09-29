<?php

namespace App\Filament\Widgets\Leadership;

use App\Filament\Pages\Leadership\Approvals;
use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use App\Support\RoleAccess;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PendingApprovals extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 2;

    protected static ?string $heading = 'Menunggu keputusan Anda';

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasAnyRole(array_diff(RoleAccess::LEADERSHIP, ['admin']));
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Ticket::query()
                ->with(['service:id,name', 'user:id,name'])
                ->whereIn('id', Ticket::approvableBy(auth()->user())->pluck('id')))
            ->defaultSort('created_at')
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')->label('No. Tiket')->weight('semibold'),
                Tables\Columns\TextColumn::make('service.name')->label('Layanan')->wrap(),
                Tables\Columns\TextColumn::make('user.name')->label('Pemohon'),
                Tables\Columns\TextColumn::make('created_at')->label('Diajukan')->since(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('all')->label('Buka menu Persetujuan')->url(Approvals::getUrl())->link(),
            ])
            ->recordUrl(fn (Ticket $record) => TicketResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('Tidak ada permohonan yang menunggu keputusan')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
