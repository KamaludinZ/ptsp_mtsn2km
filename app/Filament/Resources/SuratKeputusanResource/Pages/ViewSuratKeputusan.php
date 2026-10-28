<?php

namespace App\Filament\Resources\SuratKeputusanResource\Pages;

use App\Filament\Resources\SuratKeputusanResource;
use App\Models\SuratKeputusan;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

/** Rincian SK: one SK of the register with its scan archive and who recorded it. */
class ViewSuratKeputusan extends ViewRecord
{
    protected static string $resource = SuratKeputusanResource::class;

    public function getTitle(): string
    {
        return 'SK ' . $this->getRecord()->nomor_sk;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Ubah')
                ->modalHeading(fn (SuratKeputusan $record) => 'Data SK ' . $record->nomor_sk)
                ->using(fn (SuratKeputusan $record, array $data, Actions\EditAction $action) => SuratKeputusanResource::saveCorrection($record, $data, $action)),
            SuratKeputusanResource::deleteAction(Actions\DeleteAction::make()),
        ];
    }
}
