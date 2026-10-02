<?php

namespace App\Filament\Resources\VisitorResource\Widgets;

use App\Filament\Resources\VisitorResource;
use App\Models\Visitor;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Model;

/** Riwayat kunjungan: the guest's other visits, matched by phone number. */
class VisitHistory extends TableWidget
{
    public ?Model $record = null;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Riwayat kunjungan tamu ini';

    public function table(Table $table): Table
    {
        $visitor = $this->record;

        return $table
            ->query(fn () => Visitor::query()
                ->whereKeyNot($visitor?->getKey())
                ->where(fn ($q) => filled($visitor?->phone)
                    ? $q->where('phone', $visitor->phone)
                    : $q->whereRaw('1 = 0')))
            ->defaultSort('check_in_time', 'desc')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('check_in_time')->label('Masuk')->dateTime('d M Y H:i'),
                Tables\Columns\TextColumn::make('check_out_time')->label('Keluar')->time('H:i')->placeholder('–'),
                Tables\Columns\TextColumn::make('purpose')->label('Keperluan')->wrap()->limit(80),
                Tables\Columns\TextColumn::make('person_to_meet')->label('Menemui')->placeholder('–'),
                Tables\Columns\TextColumn::make('notes')->label('Catatan')->wrap()->limit(80)->placeholder('–'),
            ])
            ->recordUrl(fn (Visitor $record) => VisitorResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('Kunjungan pertama')
            ->emptyStateDescription('Belum ada kunjungan lain dengan nomor HP yang sama.');
    }
}
