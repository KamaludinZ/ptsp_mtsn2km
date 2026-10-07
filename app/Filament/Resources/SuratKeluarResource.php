<?php

namespace App\Filament\Resources;

use App\Filament\Forms\PersuratanFields;
use App\Filament\Resources\SuratKeluarResource\Pages;
use App\Models\SuratKeluar;
use App\Services\SuratKeluarService;
use App\Support\SuratKeluarNumber;
use App\Support\TicketDocuments;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

/**
 * Surat keluar: the outgoing-letter register. Numbers run 1-9999 and
 * restart every year; Tata Usaha (back office) keeps the register.
 */
class SuratKeluarResource extends Resource
{
    protected static ?string $model = SuratKeluar::class;

    protected static ?string $slug = 'surat-keluar';

    protected static ?string $navigationIcon = 'heroicon-o-envelope-open';

    protected static ?string $navigationGroup = 'Persuratan';

    protected static ?string $navigationLabel = 'Surat Keluar';

    protected static ?string $modelLabel = 'surat keluar';

    protected static ?string $pluralModelLabel = 'Surat Keluar';

    protected static ?string $recordTitleAttribute = 'nomor_surat';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('pembuat:id,name');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('nomor')->label('Nomor surat')
                ->content(fn (?SuratKeluar $record) => $record?->nomor_surat),
            Forms\Components\DatePicker::make('tanggal_surat')->label('Tanggal surat')
                ->native(false)->displayFormat('d M Y')->required()
                ->helperText('Harus dalam tahun nomor surat.')
                ->minDate(fn (?SuratKeluar $record) => $record ? Carbon::create($record->tahun)->startOfYear() : null)
                ->maxDate(fn (?SuratKeluar $record) => $record ? Carbon::create($record->tahun)->endOfYear() : null),
            PersuratanFields::tujuan()->required(),
            Forms\Components\TextInput::make('perihal')->label('Perihal')->required()->maxLength(255),
            Forms\Components\Grid::make(2)->schema([
                PersuratanFields::jenis()->live(debounce: 400),
                PersuratanFields::klasifikasi(),
            ]),
            // The variable ({v}/{V}) the chosen jenis surat asks for; required ones block saving when empty.
            Pages\ListSuratKeluar::variableField(),
            Forms\Components\Textarea::make('lampiran')->label('Lampiran')->rows(2)->maxLength(1000)
                ->helperText('Keterangan lampiran yang tertulis di surat, mis. "1 berkas".'),
            static::attachmentUpload(),
            PersuratanFields::tembusan(),
            Forms\Components\Textarea::make('keterangan')->label('Keterangan')->rows(2)->maxLength(1000),
            Forms\Components\Placeholder::make('pembuat')->label('Pembuat')
                ->content(fn (?SuratKeluar $record) => $record?->pembuat?->name ?? auth()->user()->name),
        ])->columns(1);
    }

    /** Uploaded attachments kept privately; their original names go to berkas_lampiran_nama. */
    public static function attachmentUpload(): Forms\Components\FileUpload
    {
        return Forms\Components\FileUpload::make('berkas_lampiran')->label('Berkas lampiran')
            ->multiple()
            ->maxFiles(10)
            ->disk('local')
            ->directory('surat-keluar')
            ->visibility('private')
            ->storeFileNamesIn('berkas_lampiran_nama')
            ->acceptedFileTypes(TicketDocuments::REQUIREMENT_MIME_TYPES)
            ->maxSize(10240)
            ->helperText('PDF, gambar, atau Word; maks. 10 berkas @ 10 MB.');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Surat')
                ->icon('heroicon-o-envelope-open')
                ->columns(['default' => 1, 'sm' => 2, 'lg' => 4])
                ->schema([
                    TextEntry::make('nomor_surat')->label('Nomor surat')->copyable()->weight('semibold'),
                    TextEntry::make('tanggal_surat')->label('Tanggal surat')->date('d M Y'),
                    TextEntry::make('jenis_surat')->label('Jenis')->badge()->color('gray')->placeholder('–'),
                    TextEntry::make('klasifikasi')->label('Klasifikasi')->placeholder('–'),
                    TextEntry::make('tujuan_surat')->label('Tujuan')->placeholder('Belum diisi')->columnSpanFull(),
                    TextEntry::make('perihal')->label('Perihal')->placeholder('Belum diisi')->weight('semibold')->columnSpanFull(),
                    TextEntry::make('tembusan')->label('Tembusan')->placeholder('–')
                        ->formatStateUsing(fn (string $state) => new HtmlString(collect(preg_split('/\R/', $state))->filter()->map(fn ($line, $i) => ($i + 1) . '. ' . e($line))->implode('<br>')))
                        ->columnSpan(['default' => 1, 'sm' => 2]),
                    TextEntry::make('keterangan')->label('Keterangan')->placeholder('–')->columnSpan(['default' => 1, 'sm' => 2]),
                ]),
            Section::make('Lampiran')
                ->icon('heroicon-o-paper-clip')
                ->schema([
                    TextEntry::make('lampiran')->label('Keterangan lampiran')->placeholder('–'),
                    TextEntry::make('berkas')->label('Berkas')
                        ->state(function (SuratKeluar $record) {
                            $files = $record->berkas_lampiran ?? [];

                            if (! $files) {
                                return 'Belum ada berkas diunggah.';
                            }

                            return new HtmlString(collect($files)->map(fn (string $path, int $index) => sprintf(
                                '<a href="%s" target="_blank" rel="noopener" style="color:rgb(var(--primary-600));text-decoration:underline">%s</a> · <a href="%s" style="color:rgb(var(--primary-600))">Unduh</a>',
                                e(route('surat-keluar.lampiran', [$record, $index])),
                                e($record->attachmentName($path)),
                                e(route('surat-keluar.lampiran', [$record, $index, 'unduh' => 1])),
                            ))->implode('<br>'));
                        }),
                ]),
            Section::make('Pencatatan')
                ->icon('heroicon-o-user')
                ->columns(['default' => 1, 'sm' => 3])
                ->collapsed()
                ->schema([
                    TextEntry::make('pembuat.name')->label('Pembuat')->placeholder('–'),
                    TextEntry::make('created_at')->label('Dicatat')->dateTime('d M Y H:i'),
                    TextEntry::make('updated_at')->label('Terakhir diubah')->dateTime('d M Y H:i'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Nomor urut tahunan, left of the full number: the quickest way to spot gaps or find a letter.
                Tables\Columns\TextColumn::make('nomor_urut')->label('No. Urut')
                    ->sortable()
                    ->searchable(query: fn (Builder $query, string $search) => ctype_digit(trim($search))
                        ? $query->orWhere('nomor_urut', (int) trim($search))
                        : $query)
                    ->alignCenter()
                    ->fontFamily('mono')
                    ->width('1%'),
                Tables\Columns\TextColumn::make('nomor_surat')->label('Nomor Surat')->searchable()->copyable()
                    // In number order (year, then sequence), not text order where B-10 comes before B-2.
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('tahun', $direction)->orderBy('nomor_urut', $direction))->weight('semibold')
                    ->description(fn (SuratKeluar $record) => $record->isDraft() ? 'Belum dilengkapi' : null)
                    ->extraAttributes(['class' => 'whitespace-nowrap']),
                Tables\Columns\TextColumn::make('tanggal_surat')->label('Tanggal')->date('d M Y')->sortable()
                    ->extraAttributes(['class' => 'whitespace-nowrap']),
                Tables\Columns\TextColumn::make('tujuan_surat')->label('Tujuan')->searchable()->wrap()->placeholder('–')->visibleFrom('md'),
                Tables\Columns\TextColumn::make('perihal')->label('Perihal')->searchable()->wrap()->limit(80)->placeholder('–'),
                Tables\Columns\TextColumn::make('jenis_surat')->label('Jenis')->badge()->color('gray')->placeholder('–')->visibleFrom('lg'),
                Tables\Columns\TextColumn::make('klasifikasi')->label('Klasifikasi')->searchable()->placeholder('–')->toggleable()->visibleFrom('lg'),
                Tables\Columns\TextColumn::make('pembuat.name')->label('Pembuat')->toggleable()->visibleFrom('xl'),
            ])
            ->defaultSort('nomor_urut', 'desc')
            ->searchPlaceholder('Cari no. urut, nomor surat, tujuan, perihal')
            ->filtersFormColumns(2)
            ->filters([
                Tables\Filters\SelectFilter::make('tahun')->label('Tahun')
                    ->options(fn () => SuratKeluar::query()->distinct()->orderByDesc('tahun')->pluck('tahun', 'tahun')->all() ?: [now()->year => now()->year])
                    ->default(now()->year),
                Tables\Filters\SelectFilter::make('jenis_surat')->label('Jenis surat')
                    ->options(fn () => SuratKeluar::whereNotNull('jenis_surat')->distinct()->orderBy('jenis_surat')->pluck('jenis_surat', 'jenis_surat')),
                Tables\Filters\SelectFilter::make('klasifikasi')->label('Klasifikasi')
                    ->options(fn () => SuratKeluar::whereNotNull('klasifikasi')->distinct()->orderBy('klasifikasi')->pluck('klasifikasi', 'klasifikasi')),
                Tables\Filters\SelectFilter::make('pembuat_id')->label('Pembuat')
                    ->relationship('pembuat', 'name')->searchable()->preload(),
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari tanggal'),
                        Forms\Components\DatePicker::make('until')->label('Sampai tanggal'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('tanggal_surat', '>=', $date))
                        ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('tanggal_surat', '<=', $date)))
                    ->indicateUsing(fn (array $data) => array_filter([
                        $data['from'] ? 'Dari ' . Carbon::parse($data['from'])->translatedFormat('j M Y') : null,
                        $data['until'] ? 'Sampai ' . Carbon::parse($data['until'])->translatedFormat('j M Y') : null,
                    ])),
                Tables\Filters\TernaryFilter::make('draft')->label('Kelengkapan')
                    ->trueLabel('Belum dilengkapi')->falseLabel('Sudah lengkap')
                    ->queries(
                        true: fn (Builder $q) => $q->where(fn (Builder $w) => $w->whereNull('perihal')->orWhereNull('tujuan_surat')),
                        false: fn (Builder $q) => $q->whereNotNull('perihal')->whereNotNull('tujuan_surat'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Detail'),
                Tables\Actions\Action::make('lampiran')
                    ->label(fn (SuratKeluar $record) => 'Lampiran (' . count($record->berkas_lampiran ?? []) . ')')
                    ->icon('heroicon-m-paper-clip')
                    ->color('gray')
                    ->visible(fn (SuratKeluar $record) => filled($record->berkas_lampiran))
                    ->url(fn (SuratKeluar $record) => count($record->berkas_lampiran) === 1
                        ? route('surat-keluar.lampiran', [$record, 0])
                        : static::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(fn (SuratKeluar $record) => count($record->berkas_lampiran) === 1),
                Tables\Actions\EditAction::make()
                    ->label(fn (SuratKeluar $record) => $record->isDraft() ? 'Lengkapi' : 'Ubah')
                    ->modalHeading(fn (SuratKeluar $record) => 'Data surat ' . $record->nomor_surat)
                    ->using(fn (SuratKeluar $record, array $data) => app(SuratKeluarService::class)->describe($record, $data)),
            ])
            ->emptyStateHeading('Belum ada surat keluar')
            ->emptyStateDescription('Ambil nomor surat untuk mulai mencatat surat keluar tahun ini.')
            ->emptyStateIcon('heroicon-o-envelope-open');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuratKeluar::route('/'),
            'view' => Pages\ViewSuratKeluar::route('/{record}'),
        ];
    }
}
