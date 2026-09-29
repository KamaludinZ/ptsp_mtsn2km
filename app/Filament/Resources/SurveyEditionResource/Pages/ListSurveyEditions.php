<?php

namespace App\Filament\Resources\SurveyEditionResource\Pages;

use App\Filament\Resources\SurveyEditionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSurveyEditions extends ListRecords
{
    protected static string $resource = SurveyEditionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
