<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /** Akun pemohon: di sinilah data yang dibaca loket (pemohon terdaftar) diperbaiki. */
    public function getSubheading(): ?string
    {
        return $this->getRecord()->isApplicant()
            ? 'Akun pemohon. Nama, email, dan nomor WhatsApp di sini yang tampil (terkunci) saat loket memilih pemohon terdaftar; perbaikan di sini langsung berlaku di loket.'
            : null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('applicants')
                ->label('Kembali ke tab Pemohon')
                ->icon('heroicon-m-arrow-uturn-left')
                ->color('gray')
                ->url(fn () => UserResource::getUrl('index', ['activeTab' => 'pemohon']))
                ->visible(fn () => $this->getRecord()->isApplicant()),
            \App\Filament\Actions\ImpersonateAction::page(),
            // Nobody deletes their own account here.
            Actions\DeleteAction::make()->hidden(fn () => $this->getRecord()->is(auth()->user())),
        ];
    }
}
