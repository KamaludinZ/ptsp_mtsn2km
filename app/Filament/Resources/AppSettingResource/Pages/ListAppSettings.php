<?php

namespace App\Filament\Resources\AppSettingResource\Pages;

use App\Filament\Resources\AppSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppSettings extends ListRecords
{
    protected static string $resource = AppSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Pengaturan')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTitle(): string
    {
        return 'Pengaturan Aplikasi';
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/' => 'Beranda',
            '/admin' => 'Admin',
            'Pengaturan Aplikasi',
        ];
    }
}