<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    /** Isian tab "Standar Pelayanan", disimpan ke service_components. */
    protected array $standard = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['standard'] = $this->record->standardComponents();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->standard = Arr::pull($data, 'standard', []) ?? [];

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncStandardComponents($this->standard);
    }
}
