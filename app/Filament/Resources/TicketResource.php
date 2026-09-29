<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Models\Service;
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

/**
 * The service tickets every staff role works from (Modul 7-9): the back
 * office processes them, leaders decide approvals and the counter hands the
 * finished products over. What each role may do is decided per action.
 */
class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $slug = 'tiket';

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Back Office';

    protected static ?string $navigationLabel = 'Tiket Layanan';

    protected static ?string $modelLabel = 'tiket';

    protected static ?string $pluralModelLabel = 'Tiket Layanan';

    protected static ?string $recordTitleAttribute = 'ticket_number';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        if (! auth()->user()?->can('backoffice.access')) {
            return null;
        }

        $count = Ticket::open()->whereNull('assigned_to_id')->count();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Tiket aktif yang belum ditugaskan';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user:id,name,email,whatsapp_number', 'service:id,name', 'assignedTo:id,name']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['ticket_number', 'user.name'];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('No. Tiket')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('semibold'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pemohon')
                    ->searchable()
                    ->description(fn (Ticket $record) => $record->user?->whatsapp_number),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Layanan')
                    ->searchable()
                    ->wrap()
                    ->limit(40),
                Tables\Columns\TextColumn::make('mode')
                    ->label('Jalur')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => TicketLabels::mode($state))
                    ->color(fn (?string $state) => $state === 'offline' ? 'warning' : 'info'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => TicketLabels::status($state))
                    ->color(fn (?string $state) => TicketLabels::statusColor($state)),
                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioritas')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => TicketLabels::priority($state))
                    ->color(fn (?string $state) => TicketLabels::priorityColor($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Petugas')
                    ->placeholder('Belum ditugaskan')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('estimated_completion_date')
                    ->label('Target selesai')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn (Ticket $record) => $record->isOverdue() ? 'danger' : null)
                    ->icon(fn (Ticket $record) => $record->isOverdue() ? 'heroicon-m-exclamation-triangle' : null)
                    ->placeholder('–'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(TicketLabels::STATUSES),
                Tables\Filters\SelectFilter::make('service_id')
                    ->label('Layanan')
                    ->options(fn () => Service::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('mode')
                    ->label('Jalur')
                    ->options(TicketLabels::MODES),
                Tables\Filters\SelectFilter::make('priority')
                    ->label('Prioritas')
                    ->options(TicketLabels::PRIORITIES),
                Tables\Filters\Filter::make('created_at')
                    ->label('Tanggal pengajuan')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('Dari'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Buka'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->recordUrl(fn (Ticket $record) => static::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('Tidak ada tiket')
            ->emptyStateIcon('heroicon-o-ticket');
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
                    TextEntry::make('mode')->label('Jalur')->formatStateUsing(fn (?string $state) => TicketLabels::mode($state)),
                    TextEntry::make('priority')->label('Prioritas')->badge()
                        ->formatStateUsing(fn (?string $state) => TicketLabels::priority($state))
                        ->color(fn (?string $state) => TicketLabels::priorityColor($state)),
                    TextEntry::make('created_at')->label('Diajukan')->dateTime('d M Y H:i'),
                    TextEntry::make('estimated_completion_date')->label('Target selesai')->date('d M Y')->placeholder('–')
                        ->color(fn (Ticket $record) => $record->isOverdue() ? 'danger' : null),
                    TextEntry::make('actual_completion_date')->label('Selesai')->date('d M Y')->placeholder('Belum selesai'),
                    TextEntry::make('assignedTo.name')->label('Petugas')->placeholder('Belum ditugaskan'),
                ]),
            Grid::make(['default' => 1, 'lg' => 2])->schema([
                Section::make('Pemohon')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user.name')->label('Nama'),
                        TextEntry::make('user.user_type')->label('Kategori')->formatStateUsing(fn (?string $state) => \App\Services\FrontDeskService::APPLICANT_TYPES[$state] ?? $state)->placeholder('–'),
                        TextEntry::make('user.email')->label('Email')->copyable()
                            ->formatStateUsing(fn (?string $state) => str_ends_with((string) $state, '@walkin.local') ? '–' : $state),
                        TextEntry::make('user.whatsapp_number')->label('WhatsApp')->placeholder('–')->copyable(),
                    ]),
                Section::make('Persetujuan pimpinan')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->columns(2)
                    ->visible(fn (Ticket $record) => (bool) $record->approval_required)
                    ->schema([
                        TextEntry::make('approval_status')->label('Keputusan')->badge()
                            ->formatStateUsing(fn (?string $state) => match ($state) {
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                default => 'Menunggu',
                            })
                            ->color(fn (?string $state) => match ($state) {
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'warning',
                            })
                            ->default('pending'),
                        TextEntry::make('approver.name')->label('Oleh')->placeholder('–'),
                        TextEntry::make('signature_type')->label('Tanda tangan')->formatStateUsing(fn (?string $state) => strtoupper((string) $state))->placeholder('–'),
                        TextEntry::make('approved_at')->label('Tanggal')->dateTime('d M Y H:i')->placeholder('–'),
                        TextEntry::make('approval_notes')->label('Catatan')->placeholder('–')->columnSpanFull(),
                    ]),
            ]),
            Section::make('Keterangan permohonan')
                ->icon('heroicon-o-chat-bubble-bottom-center-text')
                ->schema([
                    TextEntry::make('notes')->hiddenLabel()->placeholder('Tidak ada keterangan.')->prose(),
                ]),
            Grid::make(['default' => 1, 'lg' => 2])->schema([
                Section::make('Berkas')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        RepeatableEntry::make('files')
                            ->hiddenLabel()
                            ->contained(false)
                            ->placeholder('Belum ada berkas.')
                            ->schema([
                                TextEntry::make('file_name')
                                    ->hiddenLabel()
                                    ->icon('heroicon-m-document-arrow-down')
                                    ->color('primary')
                                    ->url(fn ($record) => route('documents.ticket-file', $record))
                                    ->openUrlInNewTab()
                                    ->helperText(fn ($record) => 'Diunggah ' . $record->created_at?->format('d M Y H:i')),
                            ]),
                    ]),
                Section::make('Hasil layanan')
                    ->icon('heroicon-o-document-check')
                    ->schema([
                        TextEntry::make('output.output_description')
                            ->hiddenLabel()
                            ->default('Unduh hasil layanan')
                            ->icon('heroicon-m-arrow-down-tray')
                            ->color('primary')
                            ->url(fn (Ticket $record) => $record->output ? route('documents.ticket-output', $record->output) : null)
                            ->openUrlInNewTab()
                            ->visible(fn (Ticket $record) => (bool) $record->output),
                        TextEntry::make('ready_for_pickup')
                            ->hiddenLabel()
                            ->state(fn (Ticket $record) => match (true) {
                                $record->status === 'completed' && $record->ready_for_pickup => 'Menunggu diambil pemohon di loket.',
                                $record->status === 'completed' => 'Sudah selesai.',
                                default => 'Belum ada hasil layanan.',
                            })
                            ->visible(fn (Ticket $record) => ! $record->output),
                    ]),
            ]),
            Section::make('Langkah workflow')
                ->icon('heroicon-o-queue-list')
                ->collapsible()
                ->visible(fn (Ticket $record) => $record->workflowSteps->isNotEmpty())
                ->schema([
                    RepeatableEntry::make('workflowSteps')
                        ->hiddenLabel()
                        ->columns(3)
                        ->schema([
                            TextEntry::make('workflowStep.name')->label('Langkah'),
                            TextEntry::make('status')->label('Status')->badge()
                                ->formatStateUsing(fn (?string $state, $record) => $record->completed_at ? 'Selesai' : 'Menunggu')
                                ->color(fn ($record) => $record->completed_at ? 'success' : 'gray'),
                            TextEntry::make('completed_at')->label('Selesai')->dateTime('d M Y H:i')->placeholder('–'),
                        ]),
                ]),
            Section::make('Riwayat')
                ->icon('heroicon-o-clock')
                ->collapsible()
                ->schema([
                    RepeatableEntry::make('logs')
                        ->hiddenLabel()
                        ->columns(['default' => 1, 'md' => 4])
                        ->placeholder('Belum ada riwayat.')
                        ->schema([
                            TextEntry::make('created_at')->label('Waktu')->dateTime('d M Y H:i'),
                            TextEntry::make('action')->label('Kegiatan')->formatStateUsing(fn (?string $state) => TicketLabels::logAction($state)),
                            TextEntry::make('performer.name')->label('Oleh')->placeholder('Sistem'),
                            TextEntry::make('notes')->label('Catatan')->placeholder('–'),
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
