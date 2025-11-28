<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitorResource\Pages;
use App\Models\Visitor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VisitorResource extends Resource
{
    protected static ?string $model = Visitor::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Pengunjung';

    protected static ?string $navigationGroup = 'Manajemen Pengunjung';

    protected static ?string $pluralModelLabel = 'Pengunjung';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengunjung')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Telepon')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\Select::make('type')
                            ->label('Tipe Pengunjung')
                            ->options([
                                'internal' => 'Internal (Guru/Pegawai)',
                                'external' => 'Eksternal',
                                'applicant' => 'Pemohon Layanan',
                                'official' => 'Pejabat/Instansi',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('organization')
                            ->label('Instansi/Organisasi')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Kunjungan')
                    ->schema([
                        Forms\Components\TextInput::make('purpose')
                            ->label('Tujuan Kunjungan')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('note')
                            ->label('Catatan Tambahan')
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('check_in_time')
                            ->label('Waktu Check-in')
                            ->default(now())
                            ->required(),
                        Forms\Components\DateTimePicker::make('check_out_time')
                            ->label('Waktu Check-out'),
                        Forms\Components\Toggle::make('is_checked_out')
                            ->label('Sudah Check-out')
                            ->default(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Keperluan')
                    ->schema([
                        Forms\Components\TextInput::make('service_needed')
                            ->label('Layanan yang Dibutuhkan')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('with_person')
                            ->label('Bertemu Dengan')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Aktif',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->default('active')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'internal' => 'info',
                        'external' => 'warning',
                        'applicant' => 'success',
                        'official' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'internal' => 'Internal',
                        'external' => 'Eksternal',
                        'applicant' => 'Pemohon',
                        'official' => 'Pejabat',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('purpose')
                    ->label('Tujuan')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('organization')
                    ->label('Instansi')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('check_in_time')
                    ->label('Check-in')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_out_time')
                    ->label('Check-out')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_checked_out')
                    ->label('Check-out')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe Pengunjung')
                    ->options([
                        'internal' => 'Internal',
                        'external' => 'Eksternal',
                        'applicant' => 'Pemohon',
                        'official' => 'Pejabat',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
                Tables\Filters\Filter::make('is_checked_out')
                    ->label('Sudah Check-out')
                    ->query(fn (Builder $query): Builder => $query->where('is_checked_out', true)),
                Tables\Filters\Filter::make('check_in_time')
                    ->label('Tanggal Check-in')
                    ->date(),
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
            ->defaultSort('check_in_time', 'desc');
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
            'index' => Pages\ListVisitors::route('/'),
            'create' => Pages\CreateVisitor::route('/create'),
            'view' => Pages\ViewVisitor::route('/{record}'),
            'edit' => Pages\EditVisitor::route('/{record}/edit'),
        ];
    }
}