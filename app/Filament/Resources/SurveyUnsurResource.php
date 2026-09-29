<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SurveyUnsurResource\Pages;
use App\Models\SurveyUnsur;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/** The unsur (service elements) SKM and SPAK questions are grouped by. */
class SurveyUnsurResource extends Resource
{
    use \App\Filament\Concerns\AdminOnly;

    protected static ?string $model = SurveyUnsur::class;

    protected static ?string $slug = 'survei/unsur';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Manajemen Survey';

    protected static ?string $navigationLabel = 'Unsur Survei';

    protected static ?string $modelLabel = 'unsur';

    protected static ?string $pluralModelLabel = 'Unsur Survei';

    protected static ?int $navigationSort = 2;

    public const TYPES = ['skm' => 'SKM', 'spak' => 'SPAK'];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('survey_type')->label('Survei')->options(self::TYPES)->required(),
                    Forms\Components\TextInput::make('code')->label('Kode')->required()->maxLength(50)
                        ->unique(ignoreRecord: true)
                        ->helperText('Contoh: skm-1'),
                    Forms\Components\TextInput::make('name')->label('Nama unsur')->required()->maxLength(255),
                    Forms\Components\TextInput::make('order')->label('Urutan')->numeric()->minValue(0)->default(0),
                    Forms\Components\Textarea::make('description')->label('Keterangan')->maxLength(1000)->rows(2)->columnSpanFull(),
                    Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Kode')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Unsur')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('survey_type')->label('Survei')->badge()
                    ->formatStateUsing(fn (?string $state) => self::TYPES[$state] ?? $state)
                    ->color(fn (?string $state) => $state === 'spak' ? 'warning' : 'success'),
                Tables\Columns\TextColumn::make('questions_count')->label('Pertanyaan')->counts('questions')->alignEnd(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->defaultSort('order')
            ->filters([
                Tables\Filters\SelectFilter::make('survey_type')->label('Survei')->options(self::TYPES),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSurveyUnsurs::route('/'),
        ];
    }
}
