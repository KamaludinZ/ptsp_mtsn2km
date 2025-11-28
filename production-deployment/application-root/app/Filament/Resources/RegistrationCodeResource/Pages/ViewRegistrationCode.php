<?php

namespace App\Filament\Resources\RegistrationCodeResource\Pages;

use App\Filament\Resources\RegistrationCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRegistrationCode extends ViewRecord
{
    protected static string $resource = RegistrationCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}