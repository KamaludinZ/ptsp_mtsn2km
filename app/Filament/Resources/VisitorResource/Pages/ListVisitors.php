<?php

namespace App\Filament\Resources\VisitorResource\Pages;

use App\Filament\Resources\VisitorResource;
use App\Models\Visitor;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListVisitors extends ListRecords
{
    protected static string $resource = VisitorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Daftarkan tamu')->icon('heroicon-m-user-plus'),
        ];
    }

    public function getTabs(): array
    {
        $onSite = fn (Builder $query) => $query->whereDate('check_in_time', today())->whereNull('check_out_time');

        return [
            'hari-ini' => Tab::make('Hari ini')
                ->badge(Visitor::whereDate('check_in_time', today())->count() ?: null)
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('check_in_time', today())),
            'di-lokasi' => Tab::make('Masih di lokasi')
                ->badge($onSite(Visitor::query())->count() ?: null)
                ->badgeColor('warning')
                ->modifyQueryUsing($onSite),
            'semua' => Tab::make('Semua'),
        ];
    }
}
