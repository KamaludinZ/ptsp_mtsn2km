<?php

namespace App\Filament\Widgets\Supervision;

use App\Filament\Resources\ComplaintResource;
use App\Models\Complaint;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/** Reports nobody has picked up yet (Modul 10). */
class NewComplaints extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 31;

    protected static ?string $heading = 'Laporan yang belum ditelaah';

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasRole('supervisor');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Complaint::query()->where('status', 'submitted')->where('complaint_type', '!=', 'suggestion'))
            ->defaultSort('created_at', 'desc')
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('complaint_number')->label('No. Laporan')->weight('semibold'),
                Tables\Columns\TextColumn::make('complaint_type')->label('Jenis')->badge()
                    ->formatStateUsing(fn (?string $state) => Complaint::TYPES[$state] ?? $state)
                    ->color(fn (?string $state) => $state === 'whistleblowing' ? 'danger' : 'warning'),
                Tables\Columns\TextColumn::make('title')->label('Judul')->wrap()->limit(60),
                Tables\Columns\TextColumn::make('created_at')->label('Masuk')->since(),
            ])
            ->recordUrl(fn (Complaint $record) => ComplaintResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('Semua laporan sudah ditelaah')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
