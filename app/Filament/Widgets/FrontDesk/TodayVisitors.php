<?php

namespace App\Filament\Widgets\FrontDesk;

use App\Filament\Concerns\NotifiesActionResult;
use App\Models\Visitor;
use App\Services\FrontDeskService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/** Today's guests; those still on site can be checked out from here. */
class TodayVisitors extends TableWidget
{
    use NotifiesActionResult;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 12;

    protected static ?string $heading = 'Tamu hari ini';

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasRole('front_desk');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Visitor::query()->whereDate('check_in_time', today()))
            ->defaultSort('check_in_time', 'desc')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()
                    ->description(fn (Visitor $record) => $record->institution !== '-' ? $record->institution : null),
                Tables\Columns\TextColumn::make('purpose')->label('Keperluan')->limit(40)->wrap(),
                Tables\Columns\TextColumn::make('person_to_meet')->label('Menemui')->placeholder('–'),
                Tables\Columns\TextColumn::make('check_in_time')->label('Masuk')->time('H:i'),
                Tables\Columns\TextColumn::make('check_out_time')->label('Keluar')->time('H:i')
                    ->placeholder('Masih di lokasi')->badge(fn ($state) => ! $state)->color(fn ($state) => $state ? null : 'warning'),
            ])
            ->actions([
                Tables\Actions\Action::make('checkOut')
                    ->label('Check-out')
                    ->icon('heroicon-m-arrow-right-start-on-rectangle')
                    ->color('gray')
                    ->visible(fn (Visitor $record) => ! $record->check_out_time)
                    ->action(fn (Visitor $record) => self::attempt(
                        fn () => app(FrontDeskService::class)->checkOut($record),
                        "{$record->name} sudah check-out.",
                    )),
            ])
            ->emptyStateHeading('Belum ada tamu hari ini')
            ->emptyStateIcon('heroicon-o-user-group');
    }
}
