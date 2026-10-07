<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComplaintResource\Pages;
use App\Models\Complaint;
use App\Models\Service;
use App\Services\ComplaintService;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

/**
 * Complaints, suggestions and whistleblowing reports from the public site,
 * followed up by the complaint handlers (Modul 10). Access is decided by
 * ComplaintPolicy.
 */
class ComplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $slug = 'pengaduan';

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Pengawasan';

    protected static ?string $navigationLabel = 'Pengaduan & WBS';

    protected static ?string $modelLabel = 'laporan';

    protected static ?string $pluralModelLabel = 'Pengaduan & Whistleblowing';

    protected static ?string $recordTitleAttribute = 'complaint_number';

    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        // Reports come in through the public complaint and whistleblowing forms.
        return false;
    }

    /** Suggestions have their own menu (SuggestionResource). */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('complaint_type', '!=', 'suggestion');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->where('status', 'submitted')->count();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Laporan baru yang belum ditelaah';
    }

    public static function statusColor(?string $status): string
    {
        return \App\Support\StatusBadge::for($status, 'complaint')['color'];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('complaint_number')->label('No. Laporan')->searchable()->sortable()->weight('semibold')->copyable()
                    ->icon(fn (Complaint $record) => $record->isSecret() ? 'heroicon-m-lock-closed' : null)
                    ->iconColor('danger')
                    ->description(fn (Complaint $record) => $record->isSecret() ? 'Rahasia' : null),
                Tables\Columns\TextColumn::make('complaint_type')->label('Jenis')->badge()
                    ->formatStateUsing(fn (?string $state) => Complaint::TYPES[$state] ?? $state)
                    ->color(fn (?string $state) => match ($state) {
                        'whistleblowing' => 'danger',
                        'suggestion' => 'info',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->wrap()->limit(60),
                Tables\Columns\TextColumn::make('reporter_name')->label('Pelapor')->searchable()
                    ->formatStateUsing(fn (?string $state, Complaint $record) => $record->complaint_type === 'whistleblowing' && $record->anonymous ? 'Anonim' : $state)
                    ->placeholder('–')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (?string $state) => Complaint::STATUSES[$state] ?? $state)
                    ->color(fn (?string $state) => static::statusColor($state)),
                Tables\Columns\TextColumn::make('priority')->label('Prioritas')->badge()
                    ->formatStateUsing(fn (?string $state) => Complaint::PRIORITIES[$state] ?? $state)
                    ->color(fn (?string $state) => match ($state) {
                        'urgent' => 'danger',
                        'high' => 'warning',
                        default => 'gray',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('assignee.name')->label('Penangan')->placeholder('–')->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->label('Masuk')->dateTime('d M Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Status')->options(Complaint::STATUSES),
                Tables\Filters\SelectFilter::make('priority')->label('Prioritas')->options(Complaint::PRIORITIES),
                Tables\Filters\SelectFilter::make('service_id')->label('Layanan terkait')
                    ->options(fn () => Service::orderBy('name')->pluck('name', 'id'))->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Buka'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->emptyStateHeading('Tidak ada laporan')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Laporan rahasia')
                ->icon('heroicon-o-lock-closed')
                ->iconColor('danger')
                ->description('Identitas pelapor dan isi laporan hanya untuk penangan. Jangan diteruskan atau dibahas di luar proses tindak lanjut.')
                ->visible(fn (Complaint $record) => $record->isSecret())
                ->extraAttributes(['class' => 'ring-1 ring-danger-600/30'])
                ->schema([]),
            Section::make('Laporan')
                ->icon('heroicon-o-document-text')
                ->columns(['default' => 1, 'sm' => 2, 'lg' => 4])
                ->schema([
                    TextEntry::make('complaint_type')->label('Jenis')->badge()
                        ->formatStateUsing(fn (?string $state) => Complaint::TYPES[$state] ?? $state)
                        ->color(fn (?string $state) => match ($state) {
                            'whistleblowing' => 'danger',
                            'suggestion' => 'info',
                            default => 'warning',
                        }),
                    TextEntry::make('status')->label('Status')->badge()
                        ->formatStateUsing(fn (?string $state) => Complaint::STATUSES[$state] ?? $state)
                        ->color(fn (?string $state) => static::statusColor($state)),
                    TextEntry::make('priority')->label('Prioritas')->formatStateUsing(fn (?string $state) => Complaint::PRIORITIES[$state] ?? $state),
                    TextEntry::make('created_at')->label('Masuk')->dateTime('d M Y H:i'),
                    TextEntry::make('title')->label('Judul')->columnSpanFull()->weight('semibold'),
                    TextEntry::make('description')->label('Uraian')->columnSpanFull()->prose(),
                    TextEntry::make('service.name')->label('Layanan terkait')->placeholder('–'),
                    TextEntry::make('related_ticket_number')->label('Tiket terkait')->placeholder('–'),
                    TextEntry::make('incident_date')->label('Tanggal kejadian')->date('d M Y')->placeholder('–'),
                    TextEntry::make('incident_location')->label('Lokasi kejadian')->placeholder('–'),
                    TextEntry::make('involved_parties')->label('Pihak terlibat')->placeholder('–')->columnSpanFull(),
                ]),
            Grid::make(['default' => 1, 'lg' => 2])->schema([
                Section::make('Pelapor')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('reporter_name')->label('Nama')->placeholder('Anonim'),
                        TextEntry::make('reporter_email')->label('Email')->placeholder('–')->copyable(),
                        TextEntry::make('reporter_phone')->label('Telepon')->placeholder('–')->copyable(),
                        TextEntry::make('is_confidential')->label('Kerahasiaan')
                            ->formatStateUsing(fn ($state) => $state ? 'Identitas dirahasiakan' : 'Tidak dirahasiakan'),
                    ]),
                Section::make('Bukti')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        TextEntry::make('evidence')
                            ->hiddenLabel()
                            ->state(function (Complaint $record) {
                                $files = ComplaintService::evidence($record);

                                if (! $files) {
                                    return 'Tidak ada lampiran.';
                                }

                                return new HtmlString(collect($files)->map(fn ($path, $index) => sprintf(
                                    '<a href="%s" target="_blank" rel="noopener" style="color:rgb(var(--primary-600));text-decoration:underline">%s</a>',
                                    e(route('complaints.evidence', [$record, $index])),
                                    e(basename($path)),
                                ))->implode('<br>'));
                            }),
                    ]),
            ]),
            Section::make('Tindak lanjut')
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->columns(['default' => 1, 'sm' => 2])
                ->schema([
                    TextEntry::make('assignee.name')->label('Penangan')->placeholder('Belum ditentukan'),
                    TextEntry::make('resolved_at')->label('Diselesaikan')->dateTime('d M Y H:i')->placeholder('–'),
                    TextEntry::make('response')->label('Tanggapan kepada pelapor')->placeholder('Belum ada tanggapan.')->columnSpanFull()->prose(),
                    TextEntry::make('resolution_notes')->label('Catatan internal')->placeholder('–')->columnSpanFull(),
                ]),
            Section::make('Riwayat status')
                ->icon('heroicon-o-clock')
                ->collapsible()
                ->schema([
                    \Filament\Infolists\Components\ViewEntry::make('status_history')
                        ->hiddenLabel()
                        ->view('filament.infolists.complaint-status-history'),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComplaints::route('/'),
            'view' => Pages\ViewComplaint::route('/{record}'),
        ];
    }
}
