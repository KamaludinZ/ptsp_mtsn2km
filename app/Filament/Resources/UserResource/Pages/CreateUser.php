<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /** Accounts made by an admin are trusted: no e-mail verification step. */
    protected function afterCreate(): void
    {
        $this->getRecord()->forceFill(['email_verified_at' => now()])->save();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
