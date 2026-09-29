<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegistrationCodeResource\Pages;
use App\Models\RegistrationCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RegistrationCodeResource extends Resource
{
    use \App\Filament\Concerns\AdminOnly;

    protected static ?string $model = RegistrationCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'Kode Registrasi';

    protected static ?string $navigationGroup = 'Manajemen Sistem';

    protected static ?string $pluralModelLabel = 'Kode Registrasi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kode')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Kode')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Select::make('user_type')
                            ->label('Tipe Pengguna')
                            ->options([
                                'guru' => 'Guru',
                                'pegawai' => 'Pegawai',
                                'siswa' => 'Siswa',
                                'walimurid' => 'Wali Murid',
                                'alumni' => 'Alumni',
                                'instansi' => 'Instansi',
                                'umum' => 'Umum',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('usage_limit')
                            ->label('Batas Penggunaan')
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        Forms\Components\TextInput::make('used_count')
                            ->label('Jumlah Digunakan')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Tanggal')
                    ->schema([
                        Forms\Components\DatePicker::make('valid_from')
                            ->label('Berlaku Mulai')
                            ->default(now()),
                        Forms\Components\DatePicker::make('valid_until')
                            ->label('Berlaku Sampai'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Deskripsi')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_type')
                    ->label('Tipe Pengguna')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'guru' => 'success',
                        'pegawai' => 'info',
                        'siswa' => 'warning',
                        'walimurid' => 'gray',
                        'alumni' => 'purple',
                        'instansi' => 'indigo',
                        'umum' => 'slate',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'guru' => 'Guru',
                        'pegawai' => 'Pegawai',
                        'siswa' => 'Siswa',
                        'walimurid' => 'Wali Murid',
                        'alumni' => 'Alumni',
                        'instansi' => 'Instansi',
                        'umum' => 'Umum',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('usage_limit')
                    ->label('Batas')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('used_count')
                    ->label('Digunakan')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('valid_from')
                    ->label('Mulai')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Sampai')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_type')
                    ->label('Tipe Pengguna')
                    ->options([
                        'guru' => 'Guru',
                        'pegawai' => 'Pegawai',
                        'siswa' => 'Siswa',
                        'walimurid' => 'Wali Murid',
                        'alumni' => 'Alumni',
                        'instansi' => 'Instansi',
                        'umum' => 'Umum',
                    ]),
                Tables\Filters\Filter::make('is_active')
                    ->label('Aktif')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true)),
                Tables\Filters\Filter::make('valid_from')
                    ->form([Forms\Components\DatePicker::make('valid_from')->label('Mulai Berlaku')])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['valid_from'],
                        fn (Builder $query, $date): Builder => $query->whereDate('valid_from', $date)
                    )),
                Tables\Filters\Filter::make('valid_until')
                    ->form([Forms\Components\DatePicker::make('valid_until')->label('Sampai Berlaku')])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['valid_until'],
                        fn (Builder $query, $date): Builder => $query->whereDate('valid_until', $date)
                    )),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListRegistrationCodes::route('/'),
            'create' => Pages\CreateRegistrationCode::route('/create'),
            'view' => Pages\ViewRegistrationCode::route('/{record}'),
            'edit' => Pages\EditRegistrationCode::route('/{record}/edit'),
        ];
    }
}