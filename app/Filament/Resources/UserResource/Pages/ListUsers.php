<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getSubheading(): ?string
    {
        return 'Akun petugas dan pemohon. Peran menentukan menu yang bisa dibuka; akun nonaktif tidak bisa masuk.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah pengguna')->icon('heroicon-m-user-plus'),
        ];
    }

    public function getTabs(): array
    {
        // Filament injects closure arguments by name: the query must be called $query.
        $staff = fn (Builder $query) => $query->whereHas('roles', fn (Builder $r) => $r->whereIn('name', User::STAFF_ROLES));

        return [
            'semua' => Tab::make('Semua')->badge(User::count()),
            'petugas' => Tab::make('Petugas')->icon('heroicon-m-briefcase')
                ->badge($staff(User::query())->count())
                ->modifyQueryUsing($staff),
            'pemohon' => Tab::make('Pemohon')->icon('heroicon-m-user')
                ->badge(User::whereDoesntHave('roles', fn (Builder $r) => $r->whereIn('name', User::STAFF_ROLES))->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDoesntHave('roles', fn (Builder $r) => $r->whereIn('name', User::STAFF_ROLES))),
            'nonaktif' => Tab::make('Nonaktif')->icon('heroicon-m-no-symbol')
                ->badge(User::where('is_active', false)->count() ?: null)->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false)),
        ];
    }
}
