<?php

namespace App\Filament\Resources\RegistrationCodeResource\Pages;

use App\Filament\Resources\RegistrationCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRegistrationCodes extends ListRecords
{
    protected static string $resource = RegistrationCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}