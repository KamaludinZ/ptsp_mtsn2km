<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\RelationManagers\TemplatesRelationManager;
use App\Filament\Resources\ServiceTemplateResource\Pages;
use App\Models\Service;
use App\Models\ServiceTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Template Berkas Layanan: every blank form applicants can download, for
 * all services in one place (admin). The same templates are also managed
 * on each service's page.
 */
class ServiceTemplateResource extends Resource
{
    protected static ?string $model = ServiceTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationGroup = 'Manajemen Layanan';

    protected static ?string $navigationLabel = 'Template Berkas';

    protected static ?string $modelLabel = 'template berkas';

    protected static ?string $pluralModelLabel = 'Template Berkas Layanan';

    protected static ?string $slug = 'template-berkas';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasRole('admin');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('service:id,name,slug');
    }

    public static function form(Form $form): Form
    {
        return $form->schema(static::fields(withService: true))->columns(1);
    }

    /**
     * The template form, shared with the service page's relation manager
     * (where the service is already known).
     *
     * @return array<int, Forms\Components\Component>
     */
    public static function fields(bool $withService): array
    {
        return array_values(array_filter([
            $withService ? Forms\Components\Select::make('service_id')
                ->label('Layanan')
                ->relationship('service', 'name')
                ->searchable()
                ->preload()
                ->live()
                ->required() : null,
            Forms\Components\TextInput::make('nama')->label('Nama template')->required()->maxLength(255)
                ->placeholder('mis. Formulir permohonan surat keterangan')
                ->rules([fn (Forms\Get $get, ?ServiceTemplate $record, $livewire) => \Illuminate\Validation\Rule::unique('service_templates', 'nama')
                    ->where('service_id', $get('service_id') ?? $record?->service_id ?? (method_exists($livewire, 'getOwnerRecord') ? $livewire->getOwnerRecord()->getKey() : null))
                    ->ignore($record?->getKey())])
                ->validationMessages(['unique' => 'Layanan ini sudah punya template dengan nama yang sama.']),
            Forms\Components\Textarea::make('petunjuk')->label('Petunjuk pengisian')
                ->placeholder('mis. Isi dengan huruf kapital, tanda tangani di atas meterai, lalu pindai sebagai PDF.')
                ->helperText('Ditampilkan kepada pemohon di bawah nama template.')
                ->rows(2)
                ->maxLength(500),
            Forms\Components\Placeholder::make('current_file')
                ->label('Berkas saat ini')
                ->content(fn (ServiceTemplate $record) => new \Illuminate\Support\HtmlString(
                    '<a class="text-primary-600 underline" target="_blank" href="' . e($record->downloadUrl()) . '">' . e($record->file_name ?: basename($record->file_path)) . '</a>'
                    . ' <span class="text-gray-500">(' . e($record->fileInfo() ?? 'berkas hilang') . ')</span>'))
                ->visible(fn (?ServiceTemplate $record) => (bool) $record?->file_path),
            Forms\Components\FileUpload::make('file_path')->label(fn (?ServiceTemplate $record) => $record ? 'Ganti berkas' : 'Berkas template')
                ->disk(ServiceTemplate::DISK)
                ->directory('service-templates')
                ->visibility('private')
                ->storeFileNamesIn('file_name')
                ->acceptedFileTypes(TemplatesRelationManager::MIME_TYPES)
                ->helperText('PDF, Word, atau Excel; maksimal 5 MB. Pemohon mengunduh, melengkapi, lalu mengunggahnya saat mengajukan.')
                ->maxSize(5120)
                ->downloadable()
                ->required(),
            Forms\Components\Placeholder::make('versi_info')
                ->label('Versi')
                ->content(fn (ServiceTemplate $record) => 'Versi ' . $record->versi . ' · diperbarui ' . $record->updated_at?->translatedFormat('j M Y H:i'))
                ->helperText('Mengunggah berkas baru menaikkan versi; berkas versi lama diganti.')
                ->visible(fn (?ServiceTemplate $record) => (bool) $record),
            Forms\Components\Toggle::make('is_active')->label('Aktif — ditawarkan kepada pemohon')
                ->helperText('Matikan untuk menarik template usang dari halaman layanan dan pengajuan tanpa menghapusnya.')
                ->default(true),
            Forms\Components\Toggle::make('is_required')->label('Wajib dilengkapi pemohon')
                ->helperText('Ditandai "wajib" di halaman layanan dan saat pengajuan.'),
            Forms\Components\TextInput::make('sort')->label('Urutan')->numeric()->integer()->minValue(0)->default(0),
        ]));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('service_id')
            ->defaultGroup(Tables\Grouping\Group::make('service.name')->label('Layanan')->collapsible())
            ->reorderable('sort')
            ->columns([
                Tables\Columns\TextColumn::make('nama')->label('Template')->weight('semibold')->wrap()->searchable()
                    ->description(fn (ServiceTemplate $record) => $record->petunjuk ? \Illuminate\Support\Str::limit($record->petunjuk, 80) : $record->file_name),
                Tables\Columns\TextColumn::make('service.name')->label('Layanan')->searchable()->wrap()->toggleable(),
                Tables\Columns\TextColumn::make('info')->label('Berkas')
                    ->state(fn (ServiceTemplate $record) => $record->fileInfo())
                    ->placeholder('Berkas hilang')
                    ->color(fn (ServiceTemplate $record) => $record->fileInfo() ? null : 'danger'),
                Tables\Columns\TextColumn::make('versi')->label('Versi')->formatStateUsing(fn (int $state) => 'v' . $state)->badge()->color('gray')->sortable(),
                Tables\Columns\IconColumn::make('is_required')->label('Wajib')->boolean(),
                Tables\Columns\ToggleColumn::make('is_active')->label('Aktif'),
                Tables\Columns\TextColumn::make('updated_at')->label('Diperbarui')->since()->sortable()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('service_id')->label('Layanan')
                    ->options(fn () => Service::orderBy('name')->pluck('name', 'id'))->searchable(),
                Tables\Filters\TernaryFilter::make('is_required')->label('Wajib dilengkapi'),
                Tables\Filters\TernaryFilter::make('is_active')->label('Status')
                    ->trueLabel('Aktif')->falseLabel('Nonaktif')->placeholder('Semua'),
            ])
            ->actions([
                Tables\Actions\Action::make('download')->label('Unduh')->icon('heroicon-m-arrow-down-tray')->color('gray')
                    ->url(fn (ServiceTemplate $record) => $record->downloadUrl())->openUrlInNewTab()
                    ->visible(fn (ServiceTemplate $record) => (bool) $record->fileInfo()),
                Tables\Actions\EditAction::make()
                    ->modalHeading(fn (ServiceTemplate $record) => 'Ubah template: ' . $record->nama)
                    ->modalDescription(fn (ServiceTemplate $record) => 'Layanan: ' . $record->service?->name)
                    ->successNotificationTitle('Template diperbarui'),
                Tables\Actions\DeleteAction::make()
                    ->modalDescription('Template dan berkasnya dihapus; pemohon tidak bisa mengunduhnya lagi.'),
            ])
            ->emptyStateIcon('heroicon-o-document-duplicate')
            ->emptyStateHeading('Belum ada template berkas')
            ->emptyStateDescription('Tambahkan formulir kosong yang perlu diunduh dan dilengkapi pemohon untuk sebuah layanan.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageServiceTemplates::route('/'),
        ];
    }
}
