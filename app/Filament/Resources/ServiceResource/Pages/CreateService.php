<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    /** Isian tab "Standar Pelayanan", disimpan ke service_components setelah layanan dibuat. */
    protected array $standard = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->standard = Arr::pull($data, 'standard', []) ?? [];

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncStandardComponents($this->standard);
    }
}
