<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Models\Service;
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
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Manajemen Layanan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Layanan')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->label('Kode Layanan')
                            ->required()
                            ->maxLength(50),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Select::make('mode')
                            ->label('Mode Layanan')
                            ->options([
                                'online' => 'Online',
                                'offline' => 'Offline',
                                'hybrid' => 'Hybrid',
                            ])
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        TiptapEditor::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail Layanan')
                    ->schema([
                        TiptapEditor::make('requirements')
                            ->label('Persyaratan')
                            ->columnSpanFull(),
                        TiptapEditor::make('mechanism')
                            ->label('Mekanisme')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('processing_time')
                            ->label('Waktu Proses')
                            ->placeholder('Contoh: 3 Hari Kerja'),
                        Forms\Components\TextInput::make('fee')
                            ->label('Biaya')
                            ->numeric()
                            ->prefix('Rp'),
                        TiptapEditor::make('product')
                            ->label('Produk/Output')
                            ->columnSpanFull(),
                        TiptapEditor::make('complaint_handling')
                            ->label('Penanganan Keluhan')
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_digital_product')
                            ->label('Produk Digital'),
                    ]),

                Forms\Components\Section::make('Hak Akses')
                    ->schema([
                        Forms\Components\CheckboxList::make('user_types_allowed')
                            ->label('Tipe User yang Diizinkan')
                            ->options([
                                'guru' => 'Guru',
                                'pegawai' => 'Pegawai',
                                'siswa' => 'Siswa',
                                'walimurid' => 'Wali Murid',
                                'alumni' => 'Alumni',
                                'instansi' => 'Instansi',
                                'umum' => 'Umum',
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Pengaturan Persetujuan')
                    ->schema([
                        Forms\Components\Toggle::make('approval_required')
                            ->label('Memerlukan Persetujuan')
                            ->live(),
                        Forms\Components\CheckboxList::make('approval_roles')
                            ->label('Pimpinan yang Menyetujui')
                            ->helperText('Kosongkan untuk mengizinkan Kepala Sekolah, Kepala TU, dan Admin.')
                            ->options([
                                'kepala_sekolah' => 'Kepala Sekolah',
                                'kepala_tu' => 'Kepala TU',
                                'admin' => 'Admin',
                            ])
                            ->columns(3)
                            ->visible(fn ($get) => $get('approval_required')),
                        Forms\Components\TagsInput::make('approval_users')
                            ->label('User ID yang Bisa Menyetujui')
                            ->placeholder('Ketik user ID dan tekan Enter')
                            ->visible(fn ($get) => $get('approval_required')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Layanan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mode')
                    ->label('Mode')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'online' => 'success',
                        'offline' => 'warning',
                        'hybrid' => 'info',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('processing_time')
                    ->label('Waktu Proses')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('fee')
                    ->label('Biaya')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_digital_product')
                    ->label('Produk Digital')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('approval_required')
                    ->label('Perlu Persetujuan')
                    ->boolean(),
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
                    ->options([
                        'online' => 'Online',
                        'offline' => 'Offline',
                        'hybrid' => 'Hybrid',
                    ]),
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
