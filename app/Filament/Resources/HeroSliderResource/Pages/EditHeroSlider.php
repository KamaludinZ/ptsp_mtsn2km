<?php

namespace App\Filament\Resources\HeroSliderResource\Pages;

use App\Filament\Resources\HeroSliderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHeroSlider extends EditRecord
{
    protected static string $resource = HeroSliderResource::class;

    public function getTitle(): string
    {
        return 'Edit Hero Slider';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation(),
            Actions\ViewAction::make()
                ->label('Lihat'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}