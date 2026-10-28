<?php

namespace App\Filament\Resources\VisitorResource\Pages;

use App\Filament\Resources\VisitorResource;
use App\Services\FrontDeskService;
use Filament\Actions\Action as PageAction;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * A guest registered at the counter (Modul 1), as on the public visitor
 * book: one come to meet someone, or an applicant come for a PTSP service.
 */
class CreateVisitor extends CreateRecord
{
    protected static string $resource = VisitorResource::class;

    protected static bool $canCreateAnother = false;

    public function getTitle(): string
    {
        return 'Daftarkan Tamu';
    }

    private function forService(): bool
    {
        return ($this->data['guest_type'] ?? null) === 'pemohon';
    }

    protected function handleRecordCreation(array $data): Model
    {
        return app(FrontDeskService::class)->register($data['guest_type'], $data, auth()->user());
    }

    /** The public visitor book's messages, in Indonesian (fields named by their labels). */
    protected function messages(): array
    {
        return [
            ...collect(FrontDeskService::GUEST_MESSAGES)->filter(fn ($message, string $rule) => ! str_contains($rule, '.'))->all(),
            'data.purpose_other.required' => 'Tuliskan tujuan kunjungan tamu.',
            'data.target_service_other.required' => 'Tuliskan layanan yang dituju.',
        ];
    }

    protected function getCreateFormAction(): PageAction
    {
        return parent::getCreateFormAction()
            ->label(fn () => $this->forService() ? 'Daftarkan pemohon' : 'Daftarkan tamu');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title($this->forService() ? 'Pemohon layanan berhasil didaftarkan' : 'Tamu berhasil didaftarkan')
            ->body($this->record->name . ' — ' . $this->record->purpose)
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
