<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Filament\Pages\Services\DispositionSettings;
use App\Models\Service;
use App\Support\ServiceDisposition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use FilamentTiptapEditor\TiptapEditor;

class ServiceResource extends Resource
{
    use \App\Filament\Concerns\AdminOnly;

    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Manajemen Layanan';

    protected static ?string $navigationLabel = 'Katalog Layanan';

    protected static ?string $modelLabel = 'layanan';

    protected static ?string $pluralModelLabel = 'Katalog Layanan';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('categories:id,name')
            ->withCount(['requirements', 'templates', 'tickets as open_tickets_count' => fn (Builder $q) => $q->open()]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Layanan')
                    ->persistTabInQueryString()
                    ->columnSpanFull()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Informasi')
                            ->icon('heroicon-m-information-circle')
                            ->columns(['default' => 1, 'md' => 2])
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama layanan')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('mis. Legalisir Ijazah dan Transkrip Nilai'),
                                Forms\Components\TextInput::make('code')
                                    ->label('Kode layanan')
                                    ->required()
                                    ->maxLength(50)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Kode singkat yang unik, mis. LEG-IJZ.'),
                                Forms\Components\Select::make('categories')
                                    ->label('Kategori')
                                    ->relationship('categories', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable(),
                                Forms\Components\Select::make('mode')
                                    ->label('Jalur pengajuan')
                                    ->options(\App\Support\TicketLabels::MODES)
                                    ->helperText('Online: lewat portal. Loket: datang ke kantor. Hybrid: keduanya.')
                                    ->default('online')
                                    ->required(),
                                Forms\Components\TextInput::make('processing_time')
                                    ->label('Standar waktu penyelesaian')
                                    ->placeholder('mis. 1-3 hari kerja')
                                    ->helperText('Angka terbesar dipakai untuk target selesai permohonan.')
                                    ->maxLength(100),
                                Forms\Components\TextInput::make('fee')
                                    ->label('Biaya')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('Rp')
                                    ->placeholder('0 = gratis'),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Aktif (tampil di portal dan bisa diajukan)')
                                    ->default(true),
                                Forms\Components\Toggle::make('is_digital_product')
                                    ->label('Hasil layanan berupa berkas digital')
                                    ->helperText('Jika tidak, hasil diambil di loket.'),
                                TiptapEditor::make('description')
                                    ->label('Deskripsi')
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Tabs\Tab::make('Persyaratan & Alur')
                            ->icon('heroicon-m-clipboard-document-list')
                            ->schema([
                                TiptapEditor::make('requirements')->label('Persyaratan')
                                    ->helperText('Berkas/dokumen yang harus disiapkan pemohon.'),
                                TiptapEditor::make('mechanism')->label('Alur / mekanisme'),
                                TiptapEditor::make('product')->label('Produk / hasil layanan'),
                                TiptapEditor::make('complaint_handling')->label('Penanganan pengaduan'),
                            ]),
                        Forms\Components\Tabs\Tab::make('Pemohon')
                            ->icon('heroicon-m-users')
                            ->schema([
                                Forms\Components\CheckboxList::make('user_types_allowed')
                                    ->label('Dapat diajukan oleh')
                                    ->options(\App\Services\FrontDeskService::APPLICANT_TYPES)
                                    ->helperText('Kosongkan bila layanan terbuka untuk semua pemohon.')
                                    ->columns(['default' => 2, 'md' => 4])
                                    ->bulkToggleable(),
                            ]),
                        Forms\Components\Tabs\Tab::make('Disposisi')
                            ->icon('heroicon-m-arrow-right-circle')
                            ->schema([
                                Forms\Components\Select::make('disposition_mode')
                                    ->label('Mode disposisi')
                                    ->options(fn (?Service $record) => ServiceDisposition::MODES
                                        + ($record?->disposition_mode === 'custom' ? ['custom' => 'Khusus (diatur manual, dipertahankan)'] : []))
                                    ->default('kepsek_tu')
                                    ->helperText(fn (Forms\Get $get) => ServiceDisposition::MODE_DESCRIPTIONS[$get('disposition_mode')] ?? 'Pengaturan khusus tetap dipakai sampai Anda memilih mode lain.')
                                    ->selectablePlaceholder(false)
                                    ->live()
                                    ->required(),
                                Forms\Components\CheckboxList::make('disposition_roles')
                                    ->label('Diteruskan kepada unit')
                                    ->options(ServiceDisposition::RECIPIENTS)
                                    ->helperText('Unit back office yang biasanya menerima disposisi layanan ini. Pimpinan tetap bisa memilih unit lain.')
                                    ->columns(['default' => 2, 'md' => 3])
                                    ->visible(fn (Forms\Get $get) => $get('disposition_mode') !== 'none'),
                                Forms\Components\Radio::make('signature_recommendation')
                                    ->label('Anjuran tanda tangan berkas')
                                    ->options(['' => 'Tidak ada anjuran'] + ServiceDisposition::SIGNATURES)
                                    ->default('')
                                    ->dehydrateStateUsing(fn (?string $state) => filled($state) ? $state : null)
                                    ->inline(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Layanan')
                    ->weight('semibold')
                    ->wrap()
                    ->searchable()
                    ->sortable()
                    ->description(fn (Service $record) => collect([$record->code, $record->categories->pluck('name')->join(', ')])->filter()->join(' · ') ?: null),
                Tables\Columns\TextColumn::make('mode')
                    ->label('Jalur')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => \App\Support\TicketLabels::mode($state))
                    ->color(fn (?string $state): string => match ($state) {
                        'online' => 'success',
                        'offline' => 'warning',
                        default => 'info',
                    }),
                Tables\Columns\TextColumn::make('user_types_allowed')
                    ->label('Untuk')
                    ->state(fn (Service $record) => blank($record->user_types_allowed)
                        ? 'Semua pemohon'
                        : collect($record->user_types_allowed)->map(fn (string $type) => \App\Services\FrontDeskService::APPLICANT_TYPES[$type] ?? $type)->join(', '))
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('requirements_count')
                    ->label('Syarat')
                    ->alignCenter()
                    ->color(fn (int $state) => $state ? null : 'gray')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('templates_count')
                    ->label('Template')
                    ->alignCenter()
                    ->color(fn (int $state) => $state ? null : 'gray')
                    ->url(fn (Service $record) => static::getUrl('edit', ['record' => $record]) . '?activeRelationManager=0')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('open_tickets_count')
                    ->label('Permohonan berjalan')
                    ->alignCenter()
                    ->badge()
                    ->color(fn (int $state) => $state ? 'warning' : 'gray')
                    ->url(fn (Service $record) => TicketResource::getUrl('index', ['tableFilters[service_id][values][0]' => $record->id, 'activeTab' => 'semua'])),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->tooltip(fn (Service $record) => $record->is_active ? 'Tampil di portal dan bisa diajukan' : 'Tidak tampil di portal'),
                Tables\Columns\TextColumn::make('processing_time')
                    ->label('Waktu Proses')
                    ->placeholder('–')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('fee')
                    ->label('Biaya')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_digital_product')
                    ->label('Produk Digital')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('disposition_mode')
                    ->label('Disposisi')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ServiceDisposition::mode($state))
                    ->color(fn (string $state) => match ($state) {
                        'none' => 'gray',
                        'custom' => 'warning',
                        default => 'primary',
                    })
                    ->description(fn (Service $record) => collect([
                        ServiceDisposition::usesDefault($record) ? 'Belum diatur (aturan bawaan)' : null,
                        $record->disposition_roles ? 'Ke: ' . ServiceDisposition::recipients($record->disposition_roles) : null,
                        $record->signature_recommendation ? 'Anjuran ' . strtoupper($record->signature_recommendation) : null,
                    ])->filter()->join(' · ') ?: null)
                    ->wrap()
                    ->url(fn () => DispositionSettings::getUrl()),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('mode')
                    ->label('Jalur')
                    ->options(\App\Support\TicketLabels::MODES),
                Tables\Filters\SelectFilter::make('categories')
                    ->label('Kategori')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('has_templates')
                    ->label('Template berkas')
                    ->placeholder('Semua')
                    ->trueLabel('Punya template')
                    ->falseLabel('Tanpa template')
                    ->queries(
                        true: fn (Builder $query) => $query->has('templates'),
                        false: fn (Builder $query) => $query->doesntHave('templates'),
                    ),
                Tables\Filters\SelectFilter::make('user_type')
                    ->label('Untuk pemohon')
                    ->options(\App\Services\FrontDeskService::APPLICANT_TYPES)
                    ->query(fn (Builder $query, array $data) => filled($data['value'] ?? null) ? $query->availableFor($data['value']) : $query),
                Tables\Filters\Filter::make('is_active')
                    ->label('Hanya Aktif')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->toggle(),
                Tables\Filters\Filter::make('approval_required')
                    ->label('Perlu Persetujuan')
                    ->query(fn (Builder $query): Builder => $query->where('approval_required', true))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\Action::make('portal')
                    ->label('Lihat di portal')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Service $record) => $record->slug ? route('onlineportal.service.detail', $record->slug) : null)
                    ->openUrlInNewTab()
                    ->visible(fn (Service $record) => $record->is_active && $record->slug),
                Tables\Actions\ViewAction::make()->label('Detail'),
                Tables\Actions\EditAction::make(),
                static::activationAction(Tables\Actions\Action::class),
            ])
            ->recordUrl(fn (Service $record) => static::getUrl('view', ['record' => $record]))
            ->emptyStateIcon('heroicon-o-rectangle-stack')
            ->emptyStateHeading('Belum ada layanan')
            ->emptyStateDescription('Tambahkan layanan beserta syarat, alur, dan template berkasnya agar bisa diajukan pemohon.')
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Aktifkan')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (\Illuminate\Support\Collection $records) => $records->each(fn (Service $service) => static::setActive($service, true)))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Nonaktifkan')
                        ->icon('heroicon-m-pause-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalDescription('Layanan terpilih tidak lagi tampil di portal dan tidak bisa diajukan. Permohonan yang sudah berjalan tetap diproses.')
                        ->action(fn (\Illuminate\Support\Collection $records) => $records->each(fn (Service $service) => static::setActive($service, false)))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Aktifkan / nonaktifkan a service (table row or page header). Deactivating
     * warns about requests still in progress; they keep being processed.
     *
     * @param  class-string<Tables\Actions\Action|\Filament\Actions\Action>  $class
     */
    public static function activationAction(string $class): Tables\Actions\Action|\Filament\Actions\Action
    {
        return $class::make('toggleActive')
            ->label(fn (Service $record) => $record->is_active ? 'Nonaktifkan' : 'Aktifkan')
            ->icon(fn (Service $record) => $record->is_active ? 'heroicon-m-pause-circle' : 'heroicon-m-check-circle')
            ->color(fn (Service $record) => $record->is_active ? 'warning' : 'success')
            ->requiresConfirmation()
            ->modalHeading(fn (Service $record) => ($record->is_active ? 'Nonaktifkan ' : 'Aktifkan ') . $record->name . '?')
            ->modalDescription(function (Service $record) {
                if (! $record->is_active) {
                    return 'Layanan akan tampil kembali di portal dan bisa diajukan pemohon.';
                }
                $open = $record->tickets()->open()->count();

                return 'Layanan tidak lagi tampil di portal dan tidak bisa diajukan.'
                    . ($open ? " Masih ada {$open} permohonan berjalan; permohonan itu tetap diproses sampai selesai." : '');
            })
            ->modalSubmitActionLabel(fn (Service $record) => $record->is_active ? 'Ya, nonaktifkan' : 'Ya, aktifkan')
            ->action(function (Service $record, $action) {
                static::setActive($record, ! $record->is_active);
                $action->successNotificationTitle($record->is_active ? 'Layanan diaktifkan' : 'Layanan dinonaktifkan');
                $action->success();
            });
    }

    public static function setActive(Service $service, bool $active): void
    {
        if ($service->is_active === $active) {
            return;
        }

        $service->update(['is_active' => $active]);
        activity('audit')->causedBy(auth()->user())->performedOn($service)
            ->log(($active ? 'Mengaktifkan' : 'Menonaktifkan') . ' layanan ' . $service->name);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TemplatesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'view' => Pages\ViewService::route('/{record}'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
