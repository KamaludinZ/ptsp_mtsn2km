<?php

namespace App\Filament\Resources\SurveyQuestionResource\Pages;

use App\Filament\Resources\SurveyQuestionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSurveyQuestion extends CreateRecord
{
    protected static string $resource = SurveyQuestionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return SurveyQuestionResource::syncSurveyType($data);
    }

    protected function getRedirectUrl(): string
    {
        return SurveyQuestionResource::getUrl('index');
    }
}
