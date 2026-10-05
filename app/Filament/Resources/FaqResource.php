<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaqResource\Pages;
use App\Filament\Resources\FaqResource\RelationManagers;
use App\Models\Faq;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FaqResource extends Resource
{
    use \App\Filament\Concerns\AdminOnly;

    protected static ?string $model = Faq::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = 'Manajemen Layanan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('question')
                    ->label('Pertanyaan')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->validationMessages(['unique' => 'Pertanyaan ini sudah ada di FAQ.'])
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('answer')
                    ->label('Jawaban')
                    ->required()
                    ->disableToolbarButtons(['attachFiles'])
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('category')
                    ->label('Kelompok')
                    ->datalist(fn () => \App\Models\Faq::categories())
                    ->placeholder('mis. layanan, pengaduan, akun')
                    ->helperText('Pertanyaan dengan kelompok yang sama ditampilkan bersama.')
                    ->dehydrateStateUsing(fn (?string $state) => filled($state) ? \Illuminate\Support\Str::lower(trim($state)) : null)
                    ->maxLength(50),
                Forms\Components\TextInput::make('sort')
                    ->label('Urutan')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->default(fn () => \App\Models\Faq::nextSort())
                    ->helperText('Urutan juga bisa diatur dengan menyeret baris di daftar.'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Tampilkan di situs')
                    ->default(true),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->defaultGroup(Tables\Grouping\Group::make('category')
                ->label('Kelompok')
                ->getTitleFromRecordUsing(fn (\App\Models\Faq $record) => $record->categoryLabel())
                ->collapsible())
            ->columns([
                Tables\Columns\TextColumn::make('question')
                    ->label('Pertanyaan')
                    ->weight('semibold')
                    ->wrap()
                    ->searchable(query: fn ($query, string $search) => $query->where(fn ($q) => $q
                        ->where('question', 'ilike', "%{$search}%")->orWhere('answer', 'ilike', "%{$search}%")))
                    ->description(fn (\App\Models\Faq $record) => \Illuminate\Support\Str::limit(strip_tags((string) $record->answer), 90)),
                Tables\Columns\TextColumn::make('sort')->label('Urutan')->alignCenter()->toggleable(),
                Tables\Columns\ToggleColumn::make('is_active')->label('Tampil'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kelompok')
                    ->options(fn () => collect(\App\Models\Faq::categories())->mapWithKeys(fn (string $c) => [$c => \Illuminate\Support\Str::headline($c)])->all()),
                Tables\Filters\TernaryFilter::make('is_active')->label('Tampil di situs'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Hapus FAQ?')
                    ->modalDescription(fn (\App\Models\Faq $record) => '"' . $record->question . '" dihapus permanen dari situs.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-question-mark-circle')
            ->emptyStateHeading('Belum ada FAQ')
            ->emptyStateDescription('Tambahkan pertanyaan yang sering diajukan pemohon beserta jawabannya.');
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
            'index' => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit' => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
}
