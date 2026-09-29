<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SurveyQuestionResource\Pages;
use App\Models\SurveyQuestion;
use App\Models\SurveyUnsur;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Questions of the public satisfaction survey: respondent identity, SKM
 * (IKM) and SPAK (IPAK). Every SKM/SPAK question belongs to an unsur and is
 * answered on a four-point scale (Permenpan RB 14/2017).
 */
class SurveyQuestionResource extends Resource
{
    use \App\Filament\Concerns\AdminOnly;

    protected static ?string $model = SurveyQuestion::class;

    protected static ?string $slug = 'survei/pertanyaan';

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = 'Manajemen Survey';

    protected static ?string $navigationLabel = 'Pertanyaan Survei';

    protected static ?string $modelLabel = 'pertanyaan';

    protected static ?string $pluralModelLabel = 'Pertanyaan Survei';

    protected static ?int $navigationSort = 1;

    public const TYPES = [
        'identity' => 'Identitas responden',
        'skm' => 'SKM (kepuasan)',
        'spak' => 'SPAK (anti korupsi)',
    ];

    public const FIELD_TYPES = [
        'radio' => 'Pilihan (radio)',
        'select' => 'Daftar pilihan (select)',
        'text' => 'Teks singkat',
        'textarea' => 'Teks panjang',
        'email' => 'Email',
        'tel' => 'Nomor telepon',
        'number' => 'Angka',
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('type')
                        ->label('Bagian survei')
                        ->options(self::TYPES)
                        ->required()
                        ->live(),
                    Forms\Components\Select::make('unsur_id')
                        ->label('Unsur')
                        ->options(fn (Get $get) => SurveyUnsur::where('survey_type', $get('type'))->orderBy('order')->orderBy('id')->pluck('name', 'id'))
                        ->visible(fn (Get $get) => in_array($get('type'), ['skm', 'spak'], true))
                        ->required(fn (Get $get) => in_array($get('type'), ['skm', 'spak'], true)),
                    Forms\Components\Textarea::make('question')
                        ->label('Pertanyaan')
                        ->required()
                        ->maxLength(1000)
                        ->rows(2)
                        ->columnSpanFull(),
                    Forms\Components\Select::make('field_type')
                        ->label('Jenis jawaban')
                        ->options(self::FIELD_TYPES)
                        ->default('radio')
                        ->required()
                        ->live(),
                    Forms\Components\TextInput::make('order')
                        ->label('Urutan')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required(),
                    Forms\Components\TagsInput::make('options')
                        ->label('Pilihan jawaban')
                        ->placeholder('Ketik lalu tekan Enter')
                        ->helperText(fn (Get $get) => in_array($get('type'), ['skm', 'spak'], true)
                            ? 'Isi empat pilihan dari yang terburuk (nilai 1) sampai yang terbaik (nilai 4).'
                            : null)
                        ->visible(fn (Get $get) => in_array($get('field_type'), ['radio', 'select'], true))
                        ->required(fn (Get $get) => in_array($get('field_type'), ['radio', 'select'], true))
                        ->columnSpanFull(),
                    Forms\Components\Toggle::make('is_required')->label('Wajib diisi')->default(true),
                    Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
                ]),
        ]);
    }

    /** survey_type mirrors type; the public survey and the reports read either. */
    public static function syncSurveyType(array $data): array
    {
        $data['survey_type'] = $data['type'] ?? null;
        if (! in_array($data['type'] ?? null, ['skm', 'spak'], true)) {
            $data['unsur_id'] = null;
        }

        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order')->label('#')->sortable()->width('3rem'),
                Tables\Columns\TextColumn::make('question')->label('Pertanyaan')->searchable()->wrap()->limit(90),
                Tables\Columns\TextColumn::make('type')->label('Bagian')->badge()
                    ->formatStateUsing(fn (?string $state) => self::TYPES[$state] ?? $state)
                    ->color(fn (?string $state) => match ($state) {
                        'skm' => 'success',
                        'spak' => 'warning',
                        default => 'info',
                    }),
                Tables\Columns\TextColumn::make('unsur.name')->label('Unsur')->placeholder('–')->toggleable(),
                Tables\Columns\TextColumn::make('field_type')->label('Jawaban')
                    ->formatStateUsing(fn (?string $state) => self::FIELD_TYPES[$state] ?? $state)->toggleable(),
                Tables\Columns\ToggleColumn::make('is_active')->label('Aktif'),
            ])
            ->defaultSort('order')
            ->defaultGroup(Tables\Grouping\Group::make('type')->label('Bagian')->getTitleFromRecordUsing(fn (SurveyQuestion $record) => self::TYPES[$record->type] ?? $record->type))
            ->filters([
                Tables\Filters\SelectFilter::make('type')->label('Bagian')->options(self::TYPES),
                Tables\Filters\TernaryFilter::make('is_active')->label('Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSurveyQuestions::route('/'),
            'create' => Pages\CreateSurveyQuestion::route('/create'),
            'edit' => Pages\EditSurveyQuestion::route('/{record}/edit'),
        ];
    }
}
