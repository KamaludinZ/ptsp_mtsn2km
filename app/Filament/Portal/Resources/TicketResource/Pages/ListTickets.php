<?php

namespace App\Filament\Portal\Resources\TicketResource\Pages;

use App\Filament\Portal\Resources\TicketResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('apply')->label('Ajukan layanan')->icon('heroicon-m-plus')->url(route('onlineportal.service.catalog')),
        ];
    }
}
