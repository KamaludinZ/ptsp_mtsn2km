<?php

namespace App\Filament\Resources\SurveyUnsurResource\Pages;

use App\Filament\Resources\SurveyUnsurResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSurveyUnsurs extends ManageRecords
{
    protected static string $resource = SurveyUnsurResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
