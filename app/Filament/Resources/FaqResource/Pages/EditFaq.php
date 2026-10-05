<?php

namespace App\Filament\Resources\FaqResource\Pages;

use App\Filament\Resources\FaqResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFaq extends EditRecord
{
    protected static string $resource = FaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('public')
                ->label('Lihat di situs')
                ->icon('heroicon-m-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn () => route('public.faq'))
                ->openUrlInNewTab()
                ->visible(fn () => $this->getRecord()->is_active),
            Actions\DeleteAction::make()
                ->modalHeading('Hapus FAQ?')
                ->modalDescription(fn () => '"' . $this->getRecord()->question . '" dihapus permanen dari situs.'),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'FAQ disimpan';
    }
}
