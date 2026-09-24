<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SurveyQuestionResource\Pages;
use App\Models\SurveyQuestion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SurveyQuestionResource extends Resource
{
    protected static ?string $model = SurveyQuestion::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationLabel = 'Pertanyaan Survey';

    protected static ?string $navigationGroup = 'Manajemen Survey';

    protected static ?string $pluralModelLabel = 'Pertanyaan Survey';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pertanyaan')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Jenis Pertanyaan')
                            ->options([
                                'identity' => 'Identitas',
                                'skm' => 'SKM',
                                'spak' => 'SPAK',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\TextInput::make('question')
                            ->label('Pertanyaan')
                            ->required()
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'layanan' => 'Layanan',
                                'pegawai' => 'Pegawai',
                                'fasilitas' => 'Fasilitas',
                                'prosedur' => 'Prosedur',
                                'lainnya' => 'Lainnya',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('order')
                            ->label('Urutan')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Opsi Jawaban')
                    ->schema([
                        Forms\Components\Toggle::make('is_required')
                            ->label('Wajib Diisi')
                            ->default(true),
                        Forms\Components\Select::make('answer_type')
                            ->label('Tipe Jawaban')
                            ->options([
                                'rating' => 'Rating (1-5)',
                                'likert' => 'Likert Scale',
                                'text' => 'Teks',
                                'textarea' => 'Kotak Teks',
                                'multiple_choice' => 'Pilihan Ganda',
                                'checkbox' => 'Kotak Centang',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\Textarea::make('options')
                            ->label('Pilihan Jawaban')
                            ->helperText('Masukkan pilihan jawaban, satu per baris')
                            ->visible(fn ($get) => in_array($get('answer_type'), ['multiple_choice', 'checkbox']))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('question')
                    ->label('Pertanyaan')
                    ->searchable()
                    ->limit(100)
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'identity' => 'info',
                        'skm' => 'success',
                        'spak' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'identity' => 'Identitas',
                        'skm' => 'SKM',
                        'spak' => 'SPAK',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'layanan' => 'info',
                        'pegawai' => 'warning',
                        'fasilitas' => 'success',
                        'prosedur' => 'primary',
                        'lainnya' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'layanan' => 'Layanan',
                        'pegawai' => 'Pegawai',
                        'fasilitas' => 'Fasilitas',
                        'prosedur' => 'Prosedur',
                        'lainnya' => 'Lainnya',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('answer_type')
                    ->label('Tipe Jawaban')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'identity' => 'Identitas',
                        'skm' => 'SKM',
                        'spak' => 'SPAK',
                    ]),
                Tables\Filters\SelectFilter::make('answer_type')
                    ->label('Tipe Jawaban'),
                Tables\Filters\Filter::make('is_active')
                    ->label('Aktif')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true)),
                Tables\Filters\Filter::make('created_at')
                    ->form([Forms\Components\DatePicker::make('created_at')->label('Tanggal Dibuat')])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['created_at'],
                        fn (Builder $query, $date): Builder => $query->whereDate('created_at', $date)
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
            ->defaultSort('order', 'asc');
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
            'index' => Pages\ListSurveyQuestions::route('/'),
            'create' => Pages\CreateSurveyQuestion::route('/create'),
            'view' => Pages\ViewSurveyQuestion::route('/{record}'),
            'edit' => Pages\EditSurveyQuestion::route('/{record}/edit'),
        ];
    }
}