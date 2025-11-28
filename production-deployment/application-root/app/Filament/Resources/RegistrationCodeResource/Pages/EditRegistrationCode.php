<?php

namespace App\Filament\Resources\RegistrationCodeResource\Pages;

use App\Filament\Resources\RegistrationCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRegistrationCode extends EditRecord
{
    protected static string $resource = RegistrationCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}