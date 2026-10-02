<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\Url;

/** Roles table, plus a "Kode Registrasi" tab for the civitas sign-up code. */
class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected static string $view = 'filament.resources.role-resource.list-roles';

    #[Url(as: 'bagian')]
    public string $section = 'peran';

    public function updatedSection(): void
    {
        if (! in_array($this->section, ['peran', 'kode-registrasi'], true)) {
            $this->section = 'peran';
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->visible(fn () => $this->section === 'peran'),
        ];
    }
}
