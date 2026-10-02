<?php

namespace App\Filament\Resources\NotificationTemplateResource\Pages;

use App\Filament\Resources\NotificationTemplateResource;
use Filament\Resources\Pages\EditRecord;

class EditNotificationTemplate extends EditRecord
{
    protected static string $resource = NotificationTemplateResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return [...$data, 'updated_by' => auth()->id()];
    }

    protected function getRedirectUrl(): string
    {
        return NotificationTemplateResource::getUrl('index');
    }
}
