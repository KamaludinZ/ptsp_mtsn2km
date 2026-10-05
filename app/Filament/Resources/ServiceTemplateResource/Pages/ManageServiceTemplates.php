<?php

namespace App\Filament\Resources\ServiceTemplateResource\Pages;

use App\Filament\Resources\ServiceTemplateResource;
use App\Models\Service;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageServiceTemplates extends ManageRecords
{
    protected static string $resource = ServiceTemplateResource::class;

    public function getSubheading(): ?string
    {
        $services = Service::has('templates')->count();

        return "Formulir kosong yang diunduh pemohon dari halaman layanan dan saat mengajukan. {$services} layanan memiliki template.";
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah template')
                ->icon('heroicon-m-plus')
                ->modalHeading('Tambah template berkas')
                ->modalDescription('Unggah formulir kosong yang akan diunduh dan dilengkapi pemohon.')
                ->createAnother(false)
                ->successNotificationTitle('Template ditambahkan'),
        ];
    }
}
