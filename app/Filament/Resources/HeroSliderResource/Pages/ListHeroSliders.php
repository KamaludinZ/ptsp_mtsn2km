<?php

namespace App\Filament\Resources\HeroSliderResource\Pages;

use App\Filament\Resources\HeroSliderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHeroSliders extends ListRecords
{
    protected static string $resource = HeroSliderResource::class;

    public function getSubheading(): ?string
    {
        return 'Banner bergilir di bagian atas beranda, sesuai urutan (seret untuk mengurutkan). Tanpa slide aktif, beranda memakai hero bawaan.';
    }

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Tambah slide')];
    }
}
