<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppSettingResource\Pages;
use App\Models\AppSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AppSettingResource extends Resource
{
    protected static ?string $model = AppSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Pengaturan Aplikasi';

    protected static ?string $navigationGroup = 'Manajemen Sistem';

    protected static ?string $pluralModelLabel = 'Pengaturan Aplikasi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengaturan')
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->label('Kunci')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('display_name')
                            ->label('Nama Tampilan')
                            ->maxLength(255),
                        Forms\Components\Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'general' => 'Umum',
                                'branding' => 'Identitas & Logo',
                                'contact' => 'Kontak',
                                'social' => 'Media Sosial',
                                'operating_hours' => 'Jam Operasional',
                                'related_links' => 'Tautan Terkait',
                                'theme' => 'Tema',
                            ])
                            ->default('general')
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'text' => 'Teks',
                                'textarea' => 'Kotak Teks',
                                'email' => 'Email',
                                'url' => 'URL',
                                'boolean' => 'Ya/Tidak',
                                'select' => 'Pilihan',
                                'image' => 'Gambar',
                            ])
                            ->default('text')
                            ->required()
                            ->live(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Nilai')
                    ->schema([
                        Forms\Components\TextInput::make('value')
                            ->label('Nilai')
                            ->visible(fn ($get) => ! in_array($get('type'), ['boolean', 'textarea', 'image']))
                            ->maxLength(5000)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('value')
                            ->label('Nilai')
                            ->visible(fn ($get) => $get('type') === 'textarea')
                            ->maxLength(5000)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('value')
                            ->label('Aktif')
                            ->visible(fn ($get) => $get('type') === 'boolean')
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('value')
                            ->label('Gambar')
                            ->image()
                            ->directory('app-settings')
                            ->maxSize(4096)
                            ->visible(fn ($get) => $get('type') === 'image')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->maxLength(500)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Kunci')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('display_name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('value')
                    ->label('Nilai')
                    ->limit(50),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'text' => 'Teks',
                        'textarea' => 'Kotak Teks',
                        'email' => 'Email',
                        'url' => 'URL',
                        'boolean' => 'Ya/Tidak',
                        'select' => 'Pilihan',
                        'image' => 'Gambar',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('key', 'asc');
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
            'view' => Pages\ViewAppSetting::route('/{record}'),
            'edit' => Pages\EditAppSetting::route('/{record}/edit'),
        ];
    }
}