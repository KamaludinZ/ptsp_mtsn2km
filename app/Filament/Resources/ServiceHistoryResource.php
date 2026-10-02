<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceHistoryResource\Pages;
use App\Models\Service;
use App\Models\TicketLog;
use App\Models\User;
use App\Support\RoleAccess;
use App\Support\TicketLabels;
use Filament\Forms;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Riwayat Layanan & Audit Trail: every step recorded on every ticket
 * (ticket_logs), newest first. Read-only for everyone, so it can serve as
 * audit evidence.
 */
class ServiceHistoryResource extends Resource
{
    protected static ?string $model = TicketLog::class;

    protected static ?string $slug = 'riwayat-layanan';

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Riwayat Layanan';

    protected static ?string $modelLabel = 'riwayat layanan';

    protected static ?string $pluralModelLabel = 'Riwayat Layanan';

    protected static ?int $navigationSort = 3;

    /** Immutable: only reading is ever allowed. */
    public static function can(string $action, ?Model $record = null): bool
    {
        $user = auth()->user();

        return in_array($action, ['viewAny', 'view'], true)
            && (bool) $user
            && ($user->can('backoffice.access') || $user->can('supervision.access'));
    }

    public static function getNavigationGroup(): ?string
    {
        return auth()->user()?->can('backoffice.access') ? 'Back Office' : 'Pengawasan';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['ticket:id,ticket_number,service_id', 'ticket.service:id,name', 'performer:id,name']);
    }

    /** Detail log tiap proses: who did what, when, and with which documents. */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Grid::make(['default' => 1, 'sm' => 2])->schema([
                TextEntry::make('ticket.ticket_number')->label('No. Tiket')->weight('semibold'),
                TextEntry::make('ticket.service.name')->label('Layanan')->placeholder('–'),
                TextEntry::make('action')->label('Kegiatan')->badge()->color('gray')
                    ->formatStateUsing(fn (?string $state) => TicketLabels::logAction($state)),
                TextEntry::make('created_at')->label('Waktu')
                    ->formatStateUsing(fn ($state) => $state?->translatedFormat('l, j F Y · H:i:s')),
                TextEntry::make('performer.name')->label('Pelaku')->placeholder('Sistem')
                    ->helperText(fn (TicketLog $record) => $record->performer?->getRoleNames()
                        ->map(fn (string $role) => RoleAccess::SYSTEM_ROLES[$role] ?? $role)->join(', ') ?: null),
                TextEntry::make('status_change')->label('Perubahan status')
                    ->state(fn (TicketLog $record) => $record->statusChange() ?? 'Tidak ada perubahan status'),
            ]),
            TextEntry::make('notes')->label('Catatan')->placeholder('Tidak ada catatan.')->extraAttributes(['style' => 'white-space: pre-line']),
            ViewEntry::make('documents')->label('Berkas')->view('filament.infolists.log-documents'),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('ticket.ticket_number')->label('No. Tiket')
                    ->searchable()
                    ->weight('semibold')
                    ->url(fn (TicketLog $record) => $record->ticket && TicketResource::can('view', $record->ticket)
                        ? TicketResource::getUrl('view', ['record' => $record->ticket])
                        : null),
                Tables\Columns\TextColumn::make('ticket.service.name')->label('Layanan')->wrap()->placeholder('–'),
                Tables\Columns\TextColumn::make('action')->label('Kegiatan')->badge()->color('gray')
                    ->formatStateUsing(fn (?string $state) => TicketLabels::logAction($state)),
                Tables\Columns\TextColumn::make('to_status')->label('Status')
                    ->formatStateUsing(fn (TicketLog $record) => $record->statusChange())
                    ->placeholder('–'),
                Tables\Columns\TextColumn::make('performer.name')->label('Oleh')->searchable()->placeholder('Sistem'),
                Tables\Columns\TextColumn::make('notes')->label('Catatan')->searchable()->wrap()->limit(120)->placeholder('–'),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Cari no. tiket, pelaku, atau catatan')
            ->filters([
                Tables\Filters\SelectFilter::make('service')
                    ->label('Layanan')
                    ->options(fn () => Service::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['value'],
                        fn (Builder $query, $service) => $query->whereHas('ticket', fn (Builder $q) => $q->where('service_id', $service)),
                    )),
                Tables\Filters\SelectFilter::make('performed_by')
                    ->label('Pelaku')
                    ->options(fn () => User::whereIn('id', TicketLog::query()->select('performed_by')->distinct())->orderBy('name')->pluck('name', 'id'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('action')
                    ->label('Kegiatan')
                    ->options(TicketLabels::LOG_ACTIONS)
                    ->multiple(),
                Tables\Filters\SelectFilter::make('to_status')
                    ->label('Status')
                    ->options(TicketLabels::STATUSES),
                Tables\Filters\Filter::make('period')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari tanggal'),
                        Forms\Components\DatePicker::make('until')->label('Sampai tanggal'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date)))
                    ->indicateUsing(fn (array $data) => array_filter([
                        $data['from'] ? 'Dari ' . Carbon::parse($data['from'])->translatedFormat('j M Y') : null,
                        $data['until'] ? 'Sampai ' . Carbon::parse($data['until'])->translatedFormat('j M Y') : null,
                    ])),
            ])
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\ViewAction::make()->label('Detail')
                    ->modalHeading(fn (TicketLog $record) => 'Detail log · ' . ($record->ticket?->ticket_number ?? 'tiket')),
            ])
            ->emptyStateHeading('Belum ada riwayat layanan')
            ->emptyStateDescription('Setiap tahap permohonan akan tercatat di sini secara otomatis.')
            ->emptyStateIcon('heroicon-o-clock');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceHistory::route('/'),
        ];
    }
}
