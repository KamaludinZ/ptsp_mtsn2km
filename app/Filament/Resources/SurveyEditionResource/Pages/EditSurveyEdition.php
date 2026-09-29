<?php

namespace App\Filament\Resources\SurveyEditionResource\Pages;

use App\Filament\Resources\SurveyEditionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSurveyEdition extends EditRecord
{
    protected static string $resource = SurveyEditionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return SurveyEditionResource::prepare($data, $this->getRecord());
    }

    protected function getRedirectUrl(): string
    {
        return SurveyEditionResource::getUrl('index');
    }
}
