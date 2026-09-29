<?php

namespace App\Filament\Resources\SurveyEditionResource\Pages;

use App\Filament\Resources\SurveyEditionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSurveyEdition extends CreateRecord
{
    protected static string $resource = SurveyEditionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return SurveyEditionResource::prepare($data);
    }

    protected function getRedirectUrl(): string
    {
        return SurveyEditionResource::getUrl('index');
    }
}
