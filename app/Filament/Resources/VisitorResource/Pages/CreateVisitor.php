<?php

namespace App\Filament\Resources\VisitorResource\Pages;

use App\Filament\Resources\VisitorResource;
use App\Services\FrontDeskService;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

/** A guest who comes to meet someone at school (Modul 1). */
class CreateVisitor extends CreateRecord
{
    protected static string $resource = VisitorResource::class;

    protected static bool $canCreateAnother = false;

    public function getTitle(): string
    {
        return 'Daftarkan Tamu';
    }

    protected function handleRecordCreation(array $data): Model
    {
        return app(FrontDeskService::class)->registerGuest($data, auth()->user());
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Tamu berhasil didaftarkan')
            ->actions([
                Action::make('print')
                    ->label('Cetak kartu tamu')
                    ->url(route('visitors.print', $this->record))
                    ->openUrlInNewTab(),
            ]);
    }

    protected function getRedirectUrl(): string
    {
        return VisitorResource::getUrl('index');
    }
}
