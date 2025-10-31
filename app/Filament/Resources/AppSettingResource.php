<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppSettingResource\Pages;
use App\Models\AppSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class AppSettingResource extends Resource
{
    protected static ?string $model = AppSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $label = 'Pengaturan Aplikasi';

    protected static ?string $pluralLabel = 'Pengaturan Aplikasi';

    protected static ?string $slug = 'app-settings';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->label('Kunci')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabled(fn (string $context): bool => $context === 'edit')
                    ->helperText('Kunci unik untuk identifikasi setting'),

                Forms\Components\TextInput::make('display_name')
                    ->label('Nama Tampilan')
                    ->required()
                    ->helperText('Nama yang akan ditampilkan di form admin'),

                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(2)
                    ->helperText('Deskripsi dari pengaturan'),

                Forms\Components\Select::make('type')
                    ->label('Tipe Data')
                    ->options([
                        'text' => 'Teks',
                        'textarea' => 'Teks Panjang',
                        'number' => 'Angka',
                        'boolean' => 'Boolean (Ya/Tidak)',
                        'email' => 'Email',
                        'url' => 'URL',
                        'color' => 'Warna',
                        'file' => 'File',
                        'image' => 'Gambar',
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn (string $state, callable $set) => $set('value', null)),

                Forms\Components\Select::make('category')
                    ->label('Kategori')
                    ->options([
                        'general' => 'Umum',
                        'branding' => 'Branding & Identitas',
                        'contact' => 'Kontak',
                        'social' => 'Media Sosial',
                        'seo' => 'SEO',
                    ])
                    ->required(),

                Forms\Components\Section::make('Nilai Pengaturan')
                    ->schema([
                        Forms\Components\TextInput::make('value')
                            ->label('Nilai')
                            ->required()
                            ->visible(fn (callable $get) => in_array($get('type'), ['text', 'email', 'url', 'color', 'number']))
                            ->helperText('Masukkan nilai pengaturan'),

                        Forms\Components\Textarea::make('value')
                            ->label('Nilai')
                            ->required()
                            ->visible(fn (callable $get) => in_array($get('type'), ['textarea']))
                            ->rows(3),

                        Forms\Components\Toggle::make('value')
                            ->label('Nilai')
                            ->visible(fn (callable $get) => $get('type') === 'boolean')
                            ->helperText('Aktifkan atau nonaktifkan'),

                        Forms\Components\FileUpload::make('value')
                            ->label('File')
                            ->visible(fn (callable $get) => $get('type') === 'file')
                            ->directory('settings')
                            ->helperText('Upload file'),

                        Forms\Components\FileUpload::make('value')
                            ->label('Gambar')
                            ->visible(fn (callable $get) => $get('type') === 'image')
                            ->image()
                            ->imageEditor()
                            ->directory('settings/images')
                            ->helperText('Upload gambar'),
                    ]),

                Forms\Components\KeyValue::make('validation_rules')
                    ->label('Aturan Validasi')
                    ->keyLabel('Aturan')
                    ->valueLabel('Nilai')
                    ->helperText('Aturan validasi dalam format JSON'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Kunci')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Kunci disalin!')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('display_name')
                    ->label('Nama Tampilan')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipe')
                    ->colors([
                        'primary' => 'text',
                        'secondary' => 'textarea',
                        'success' => 'number',
                        'warning' => 'boolean',
                        'info' => 'email',
                        'danger' => 'url',
                    ]),

                Tables\Columns\BadgeColumn::make('category')
                    ->label('Kategori')
                    ->colors([
                        'primary' => 'general',
                        'success' => 'branding',
                        'info' => 'contact',
                        'warning' => 'social',
                        'danger' => 'seo',
                    ]),

                Tables\Columns\TextColumn::make('value')
                    ->label('Nilai')
                    ->limit(50)
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string =>
                        strlen($state) > 50 ? substr($state, 0, 50) . '...' : $state
                    ),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diupdate')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'text' => 'Teks',
                        'textarea' => 'Teks Panjang',
                        'number' => 'Angka',
                        'boolean' => 'Boolean',
                        'email' => 'Email',
                        'url' => 'URL',
                        'color' => 'Warna',
                        'file' => 'File',
                        'image' => 'Gambar',
                    ]),

                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'general' => 'Umum',
                        'branding' => 'Branding & Identitas',
                        'contact' => 'Kontak',
                        'social' => 'Media Sosial',
                        'seo' => 'SEO',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Pengaturan')
                    ->modalDescription('Apakah Anda yakin ingin menghapus pengaturan ini?'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Pengaturan Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus pengaturan yang dipilih?'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppSettings::route('/'),
            'create' => Pages\CreateAppSetting::route('/create'),
            'edit' => Pages\EditAppSetting::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return AppSetting::count();
    }
}