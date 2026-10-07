<?php

namespace App\Filament\Pages\Reports;

use App\Models\SurveyArchive;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

/**
 * Arsip rekap kuartalan SKM & SPAK (survey:archive-quarterly): hasil tiap
 * triwulan yang sudah dibekukan, dengan rincian nilai per pertanyaan.
 * Akses sama dengan Laporan SKM & SPAK.
 */
class SurveyArchives extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Pengawasan';

    protected static ?string $navigationLabel = 'Arsip Survei Kuartalan';

    protected static ?string $title = 'Arsip Rekap Kuartalan Survei';

    protected static ?string $slug = 'laporan/survei/arsip';

    protected static ?int $navigationSort = 6;

    protected static string $view = 'filament.pages.table-page';

    public static function canAccess(): bool
    {
        return SurveyReport::canAccess();
    }

    public function getSubheading(): ?string
    {
        return 'Rekap SKM dan SPAK yang diarsipkan setiap triwulan (metode Permenpan RB 14/2017). Arsip tidak berubah meski pertanyaan survei diperbarui.';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(SurveyArchive::query()->where('is_quarterly_archive', true))
            ->defaultSort('quarter', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('quarter')->label('Triwulan')->sortable()->weight('semibold'),
                Tables\Columns\TextColumn::make('type')->label('Survei')->badge()
                    ->formatStateUsing(fn (?string $state) => strtoupper((string) $state))
                    ->color(fn (?string $state) => $state === 'spak' ? 'warning' : 'info'),
                Tables\Columns\TextColumn::make('period_range')->label('Periode')
                    ->state(fn (SurveyArchive $a) => isset($a->data['start_date'], $a->data['end_date'])
                        ? \Illuminate\Support\Carbon::parse($a->data['start_date'])->translatedFormat('j M Y') . ' – ' . \Illuminate\Support\Carbon::parse($a->data['end_date'])->translatedFormat('j M Y')
                        : '–'),
                Tables\Columns\TextColumn::make('respondents')->label('Responden')->numeric()
                    ->state(fn (SurveyArchive $a) => (int) ($a->calculated_values['total_respondents'] ?? $a->data['responses_count'] ?? 0)),
                Tables\Columns\TextColumn::make('average')->label('Nilai rata-rata')
                    ->state(fn (SurveyArchive $a) => number_format((float) ($a->calculated_values['average'] ?? 0), 2, ',', '.')),
                Tables\Columns\TextColumn::make('percentage')->label('Indeks')->badge()
                    ->state(fn (SurveyArchive $a) => number_format((float) ($a->calculated_values['percentage'] ?? 0), 2, ',', '.') . '%')
                    ->color(fn (SurveyArchive $a) => match (true) {
                        ($a->calculated_values['percentage'] ?? 0) >= 88.31 => 'success',
                        ($a->calculated_values['percentage'] ?? 0) >= 76.61 => 'info',
                        ($a->calculated_values['percentage'] ?? 0) >= 65 => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('Diarsipkan')->dateTime('d M Y H:i')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->label('Survei')->options(['skm' => 'SKM', 'spak' => 'SPAK']),
                Tables\Filters\SelectFilter::make('year')->label('Tahun')
                    ->options(fn () => SurveyArchive::query()->where('is_quarterly_archive', true)->distinct()->orderByDesc('year')->pluck('year', 'year')->all()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Rincian')
                    ->modalHeading(fn (SurveyArchive $a) => strtoupper($a->type) . ' · ' . $a->quarter)
                    ->infolist([
                        Section::make()->columns(3)->schema([
                            TextEntry::make('respondents')->label('Responden')
                                ->state(fn (SurveyArchive $a) => (int) ($a->calculated_values['total_respondents'] ?? 0)),
                            TextEntry::make('average')->label('Nilai rata-rata')
                                ->state(fn (SurveyArchive $a) => number_format((float) ($a->calculated_values['average'] ?? 0), 2, ',', '.')),
                            TextEntry::make('percentage')->label('Indeks')
                                ->state(fn (SurveyArchive $a) => number_format((float) ($a->calculated_values['percentage'] ?? 0), 2, ',', '.') . '%'),
                        ]),
                        RepeatableEntry::make('calculated_values.scores')->label('Nilai per pertanyaan')
                            ->schema([
                                TextEntry::make('question')->hiddenLabel()->columnSpan(2),
                                TextEntry::make('average_score')->label('Rata-rata'),
                                TextEntry::make('total_responses')->label('Jawaban'),
                            ])
                            ->columns(4)
                            ->placeholder('Tidak ada rincian pertanyaan.'),
                    ]),
            ])
            ->emptyStateHeading('Belum ada arsip kuartalan')
            ->emptyStateDescription('Arsip dibuat otomatis setiap akhir triwulan oleh penjadwal aplikasi.')
            ->emptyStateIcon('heroicon-o-archive-box');
    }
}
