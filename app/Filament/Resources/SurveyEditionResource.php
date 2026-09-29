<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SurveyEditionResource\Pages;
use App\Models\SurveyEdition;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Survey editions: the quarter responses are counted in. One edition is
 * active at a time.
 */
class SurveyEditionResource extends Resource
{
    use \App\Filament\Concerns\AdminOnly;

    protected static ?string $model = SurveyEdition::class;

    protected static ?string $slug = 'survei/edisi';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Manajemen Survey';

    protected static ?string $navigationLabel = 'Edisi Survei';

    protected static ?string $modelLabel = 'edisi survei';

    protected static ?string $pluralModelLabel = 'Edisi Survei';

    protected static ?int $navigationSort = 3;

    public const QUARTERS = ['Q1' => 'Triwulan 1', 'Q2' => 'Triwulan 2', 'Q3' => 'Triwulan 3', 'Q4' => 'Triwulan 4'];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('period')->label('Triwulan')->options(self::QUARTERS)->required(),
                    Forms\Components\TextInput::make('year')->label('Tahun')->numeric()->minValue(2000)->maxValue(2100)
                        ->default(now()->year)->required(),
                    Forms\Components\Textarea::make('description')->label('Keterangan')->maxLength(1000)->rows(2)->columnSpanFull(),
                    Forms\Components\Toggle::make('is_active')->label('Jadikan edisi aktif')
                        ->helperText('Edisi lain otomatis dinonaktifkan.'),
                ]),
        ]);
    }

    /** Name and date range follow from the quarter; activating one edition deactivates the rest. */
    public static function prepare(array $data, ?SurveyEdition $record = null): array
    {
        $quarter = (int) substr($data['period'], 1);
        $start = Carbon::create((int) $data['year'], ($quarter - 1) * 3 + 1, 1)->startOfDay();

        if (! empty($data['is_active'])) {
            SurveyEdition::query()->when($record, fn ($q) => $q->whereKeyNot($record->id))->update(['is_active' => false]);
        }

        return $data + [
            'name' => "Triwulan {$quarter} {$data['year']}",
            'type' => 'quarterly',
            'start_date' => $start->toDateString(),
            'end_date' => $start->copy()->addMonths(2)->endOfMonth()->toDateString(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Edisi')->weight('semibold')->searchable(),
                Tables\Columns\TextColumn::make('start_date')->label('Mulai')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('end_date')->label('Selesai')->date('d M Y'),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
                Tables\Columns\TextColumn::make('description')->label('Keterangan')->limit(60)->placeholder('–')->toggleable(),
            ])
            ->defaultSort('start_date', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSurveyEditions::route('/'),
            'create' => Pages\CreateSurveyEdition::route('/create'),
            'edit' => Pages\EditSurveyEdition::route('/{record}/edit'),
        ];
    }
}
