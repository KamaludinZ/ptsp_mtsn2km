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
                        Forms\Components\TextInput::make('institution')
                            ->label('Instansi/Organisasi')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('institution_category')
                            ->label('Kategori Instansi')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Kunjungan')
                    ->schema([
                        Forms\Components\TextInput::make('purpose')
                            ->label('Tujuan Kunjungan')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('person_to_meet')
                            ->label('Bertemu Dengan')
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('check_in_time')
                            ->label('Waktu Check-in')
                            ->default(now())
                            ->required(),
                        Forms\Components\DateTimePicker::make('check_out_time')
                            ->label('Waktu Check-out'),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Sedang berkunjung',
                                'checked_out' => 'Sudah check-out',
                            ])
                            ->default('active')
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
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
                Tables\Columns\TextColumn::make('institution')
                    ->label('Instansi')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('purpose')
                    ->label('Tujuan')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('person_to_meet')
                    ->label('Bertemu')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('check_in_time')
                    ->label('Check-in')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_out_time')
                    ->label('Check-out')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'active' ? 'warning' : 'success')
                    ->formatStateUsing(fn (string $state): string => $state === 'active' ? 'Berkunjung' : 'Selesai'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Sedang berkunjung',
                        'checked_out' => 'Sudah check-out',
                    ]),
                Tables\Filters\Filter::make('check_in_time')
                    ->form([Forms\Components\DatePicker::make('check_in_time')->label('Tanggal Check-in')])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['check_in_time'],
                        fn (Builder $query, $date): Builder => $query->whereDate('check_in_time', $date)
                    )),
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