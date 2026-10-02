<?php

namespace App\Filament\Resources;

use App\Filament\Forms\PersuratanFields;
use App\Filament\Resources\SuratKeluarResource\Pages;
use App\Models\SuratKeluar;
use App\Support\SuratKeluarNumber;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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

    /** Numbers are never deleted: a gap in the register must stay explainable. */
    public static function can(string $action, ?Model $record = null): bool
    {
        return in_array($action, ['viewAny', 'view', 'create', 'update'], true)
            && (bool) auth()->user()?->can('backoffice.access');
    }

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
                PersuratanFields::jenis(),
                PersuratanFields::klasifikasi(),
            ]),
            Forms\Components\Textarea::make('lampiran')->label('Lampiran')->rows(2)->maxLength(1000),
            PersuratanFields::tembusan(),
            Forms\Components\Textarea::make('keterangan')->label('Keterangan')->rows(2)->maxLength(1000),
            Forms\Components\Placeholder::make('pembuat')->label('Pembuat')
                ->content(fn (?SuratKeluar $record) => $record?->pembuat?->name ?? auth()->user()->name),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_surat')->label('Nomor Surat')->searchable()->copyable()->weight('semibold')
                    ->description(fn (SuratKeluar $record) => $record->isDraft() ? 'Belum dilengkapi' : null),
                Tables\Columns\TextColumn::make('tanggal_surat')->label('Tanggal')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('tujuan_surat')->label('Tujuan')->searchable()->wrap()->placeholder('–'),
                Tables\Columns\TextColumn::make('perihal')->label('Perihal')->searchable()->wrap()->limit(80)->placeholder('–'),
                Tables\Columns\TextColumn::make('jenis_surat')->label('Jenis')->badge()->color('gray')->placeholder('–'),
                Tables\Columns\TextColumn::make('klasifikasi')->label('Klasifikasi')->searchable()->placeholder('–')->toggleable(),
                Tables\Columns\TextColumn::make('pembuat.name')->label('Pembuat')->toggleable(),
            ])
            ->defaultSort('nomor_urut', 'desc')
            ->searchPlaceholder('Cari nomor, tujuan, perihal')
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
                Tables\Actions\EditAction::make()
                    ->label(fn (SuratKeluar $record) => $record->isDraft() ? 'Lengkapi' : 'Ubah')
                    ->modalHeading(fn (SuratKeluar $record) => 'Data surat ' . $record->nomor_surat)
                    ->mutateFormDataUsing(fn (array $data, SuratKeluar $record) => [
                        ...$data,
                        // The month and classification are part of the number.
                        'nomor_surat' => SuratKeluarNumber::format($record->nomor_urut, Carbon::parse($data['tanggal_surat']), $data['klasifikasi'] ?? null),
                    ]),
            ])
            ->emptyStateHeading('Belum ada surat keluar')
            ->emptyStateDescription('Ambil nomor surat untuk mulai mencatat surat keluar tahun ini.')
            ->emptyStateIcon('heroicon-o-envelope-open');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuratKeluar::route('/'),
        ];
    }
}
