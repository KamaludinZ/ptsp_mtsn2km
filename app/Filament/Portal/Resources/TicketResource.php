<?php

namespace App\Filament\Portal\Resources;

use App\Filament\Portal\Resources\TicketResource\Pages;
use App\Models\Ticket;
use App\Support\TicketLabels;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** The applicant's own service requests, read-only. */
class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $slug = 'permohonan';

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Permohonan Saya';

    protected static ?string $modelLabel = 'permohonan';

    protected static ?string $pluralModelLabel = 'Permohonan Saya';

    protected static ?string $recordTitleAttribute = 'ticket_number';

    protected static ?int $navigationSort = 1;

    /** Applicants only ever reach their own tickets, and cannot change them here. */
    public static function can(string $action, ?Model $record = null): bool
    {
        $user = auth()->user();

        return match ($action) {
            'viewAny' => (bool) $user,
            'view' => $user && $record && (int) $record->user_id === (int) $user->id,
            default => false,
        };
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id())
            ->with(['service:id,name,processing_time', 'output']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')->label('No. Tiket')->searchable()->weight('semibold')->copyable(),
                Tables\Columns\TextColumn::make('service.name')->label('Layanan')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (?string $state) => TicketLabels::status($state))
                    ->color(fn (?string $state) => TicketLabels::statusColor($state)),
                Tables\Columns\TextColumn::make('created_at')->label('Diajukan')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('estimated_completion_date')->label('Perkiraan selesai')->date('d M Y')->placeholder('–'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Status')->options(TicketLabels::STATUSES),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Detail'),
                Tables\Actions\Action::make('download')
                    ->label('Unduh hasil')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('success')
                    ->visible(fn (Ticket $record) => $record->status === 'completed' && $record->output)
                    ->url(fn (Ticket $record) => route('documents.ticket-output', $record->output))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('Belum ada permohonan')
            ->emptyStateDescription('Pilih layanan di Katalog Layanan untuk mengajukan permohonan.')
            ->emptyStateIcon('heroicon-o-ticket')
            ->emptyStateActions([
                Tables\Actions\Action::make('catalog')->label('Buka katalog layanan')->url(route('onlineportal.service.catalog')),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Ringkasan')
                ->icon('heroicon-o-information-circle')
                ->columns(['default' => 1, 'sm' => 2, 'lg' => 4])
                ->schema([
                    TextEntry::make('status')->label('Status')->badge()
                        ->formatStateUsing(fn (?string $state) => TicketLabels::status($state))
                        ->color(fn (?string $state) => TicketLabels::statusColor($state)),
                    TextEntry::make('service.name')->label('Layanan'),
                    TextEntry::make('created_at')->label('Diajukan')->dateTime('d M Y H:i'),
                    TextEntry::make('estimated_completion_date')->label('Perkiraan selesai')->date('d M Y')->placeholder('Sesuai standar pelayanan'),
                    TextEntry::make('ready_for_pickup')->label('Pengambilan')
                        ->state(fn (Ticket $record) => $record->status === 'completed' && $record->ready_for_pickup
                            ? 'Siap diambil di loket PTSP'
                            : null)
                        ->placeholder('–')
                        ->color('success')
                        ->columnSpanFull()
                        ->visible(fn (Ticket $record) => $record->status === 'completed' && $record->ready_for_pickup),
                    TextEntry::make('approval_notes')->label('Catatan penolakan')
                        ->visible(fn (Ticket $record) => $record->status === 'rejected' && filled($record->approval_notes))
                        ->color('danger')
                        ->columnSpanFull(),
                ]),
            Section::make('Keterangan Anda')
                ->icon('heroicon-o-chat-bubble-bottom-center-text')
                ->schema([
                    TextEntry::make('notes')->hiddenLabel()->placeholder('Tidak ada keterangan.')->prose(),
                ]),
            Grid::make(['default' => 1, 'lg' => 2])->schema([
                Section::make('Berkas yang dikirim')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        RepeatableEntry::make('files')
                            ->hiddenLabel()
                            ->contained(false)
                            ->placeholder('Tidak ada berkas.')
                            ->schema([
                                TextEntry::make('file_name')
                                    ->hiddenLabel()
                                    ->icon('heroicon-m-document')
                                    ->color('primary')
                                    ->url(fn ($record) => route('documents.ticket-file', $record))
                                    ->openUrlInNewTab(),
                            ]),
                    ]),
                Section::make('Perkembangan')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        RepeatableEntry::make('logs')
                            ->hiddenLabel()
                            ->contained(false)
                            ->placeholder('Belum ada perkembangan.')
                            ->schema([
                                TextEntry::make('action')
                                    ->hiddenLabel()
                                    ->formatStateUsing(fn (?string $state, $record) => $state === 'status_changed'
                                        ? 'Status menjadi ' . TicketLabels::status($record->to_status)
                                        : TicketLabels::logAction($state))
                                    ->weight('semibold')
                                    ->helperText(fn ($record) => $record->created_at?->format('d M Y H:i')),
                            ]),
                    ]),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'view' => Pages\ViewTicket::route('/{record}'),
        ];
    }
}
