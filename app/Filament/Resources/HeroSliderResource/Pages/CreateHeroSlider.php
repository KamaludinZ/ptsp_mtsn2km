<?php

namespace App\Filament\Resources\HeroSliderResource\Pages;

use App\Filament\Resources\HeroSliderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHeroSlider extends CreateRecord
{
    protected static string $resource = HeroSliderResource::class;

    public function getTitle(): string
    {
        return 'Tambah Hero Slider';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}