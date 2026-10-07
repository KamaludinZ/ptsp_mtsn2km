<?php

namespace App\Filament\Resources\SuratKeluarResource\Widgets;

use App\Filament\Resources\SuratKeluarResource;
use App\Models\SuratKeluarBatch;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Riwayat permintaan nomor agenda: siapa meminta berapa nomor sekaligus,
 * rentang nomornya, dan berapa yang sudah diisi data suratnya.
 */
class NumberRequestHistory extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static bool $isLazy = false;

    protected static ?string $heading = 'Riwayat permintaan nomor agenda';

    public static function canView(): bool
    {
        return SuratKeluarResource::canViewAny();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(SuratKeluarBatch::query()
                ->with('creator:id,name')
                ->withCount(['letters', 'letters as filled_count' => fn (Builder $q) => $q
                    // Same rule as SuratKeluar::isDraft(): filled once it has a subject and an addressee.
                    ->whereNotNull('perihal')->where('perihal', '!=', '')
                    ->whereNotNull('tujuan_surat')->where('tujuan_surat', '!=', '')]))
            ->defaultSort('created_at', 'desc')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Diminta')->dateTime('d M Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('tahun')->label('Tahun')->sortable(),
                Tables\Columns\TextColumn::make('range')->label('Nomor urut')
                    ->state(fn (SuratKeluarBatch $b) => $b->nomor_awal === $b->nomor_akhir ? (string) $b->nomor_awal : "{$b->nomor_awal}–{$b->nomor_akhir}"),
                Tables\Columns\TextColumn::make('jumlah_diminta')->label('Jumlah')->numeric(),
                Tables\Columns\TextColumn::make('creator.name')->label('Diminta oleh')->placeholder('–'),
                Tables\Columns\TextColumn::make('filled_count')->label('Sudah diisi')
                    ->state(fn (SuratKeluarBatch $b) => "{$b->filled_count} dari {$b->letters_count}")
                    ->badge()
                    ->color(fn (SuratKeluarBatch $b) => $b->filled_count >= $b->letters_count ? 'success' : 'warning'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tahun')->label('Tahun')
                    ->options(fn () => SuratKeluarBatch::query()->distinct()->orderByDesc('tahun')->pluck('tahun', 'tahun')->all()),
            ])
            ->emptyStateHeading('Belum ada permintaan nomor')
            ->emptyStateIcon('heroicon-o-hashtag');
    }
}
