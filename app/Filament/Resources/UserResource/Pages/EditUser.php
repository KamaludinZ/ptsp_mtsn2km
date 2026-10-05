<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Nobody deletes their own account here.
            Actions\DeleteAction::make()->hidden(fn () => $this->getRecord()->is(auth()->user())),
        ];
    }
}
