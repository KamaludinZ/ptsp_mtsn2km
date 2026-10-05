<?php

namespace App\Filament\Resources\SuratKeluarResource\Pages;

use App\Filament\Resources\SuratKeluarResource;
use App\Models\SuratKeluar;
use App\Services\SuratKeluarService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSuratKeluar extends ViewRecord
{
    protected static string $resource = SuratKeluarResource::class;

    public function getTitle(): string
    {
        return 'Surat ' . $this->getRecord()->nomor_surat;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label(fn (SuratKeluar $record) => $record->isDraft() ? 'Lengkapi' : 'Ubah')
                ->modalHeading(fn (SuratKeluar $record) => 'Data surat ' . $record->nomor_surat)
                ->using(fn (SuratKeluar $record, array $data) => app(SuratKeluarService::class)->describe($record, $data)),
        ];
    }
}
