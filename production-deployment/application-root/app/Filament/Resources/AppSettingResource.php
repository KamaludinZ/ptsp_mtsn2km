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
                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'text' => 'Teks',
                                'textarea' => 'Kotak Teks',
                                'number' => 'Angka',
                                'boolean' => 'Boolean',
                                'select' => 'Pilihan',
                                'file' => 'File',
                            ])
                            ->required()
                            ->live(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Nilai')
                    ->schema([
                        Forms\Components\TextInput::make('value')
                            ->label('Nilai')
                            ->required()
                            ->visible(fn ($get) => !in_array($get('type'), ['boolean']))
                            ->maxLength(5000)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('value')
                            ->label('Aktif')
                            ->visible(fn ($get) => $get('type') === 'boolean')
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('value')
                            ->label('File')
                            ->directory('app-settings')
                            ->preserveFilenames()
                            ->maxSize(10240) // 10MB
                            ->acceptedFileTypes(['image/*', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->visible(fn ($get) => $get('type') === 'file'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Deskripsi')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Metadata')
                    ->schema([
                        Forms\Components\Toggle::make('is_public')
                            ->label('Dapat Dilihat Publik'),
                        Forms\Components\Toggle::make('is_required')
                            ->label('Wajib'),
                    ])
                    ->columns(2),
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
                Tables\Columns\TextColumn::make('name')
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
                Tables\Columns\IconColumn::make('is_public')
                    ->label('Publik')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_required')
                    ->label('Wajib')
                    ->boolean(),
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
                        'number' => 'Angka',
                        'boolean' => 'Boolean',
                        'select' => 'Pilihan',
                        'file' => 'File',
                    ]),
                Tables\Filters\Filter::make('is_public')
                    ->label('Publik')
                    ->query(fn (Builder $query): Builder => $query->where('is_public', true)),
                Tables\Filters\Filter::make('is_required')
                    ->label('Wajib')
                    ->query(fn (Builder $query): Builder => $query->where('is_required', true)),
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
            ->defaultSort('name', 'asc');
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