<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use App\Models\Service;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListServices extends ListRecords
{
    protected static string $resource = ServiceResource::class;

    public function getSubheading(): ?string
    {
        return 'Layanan PTSP yang dapat diajukan pemohon: syarat, alur, template berkas, dan pengaturan disposisinya.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah layanan')->icon('heroicon-m-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua')->badge(Service::count()),
            'aktif' => Tab::make('Aktif')->badge(Service::where('is_active', true)->count())->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true)),
            'nonaktif' => Tab::make('Nonaktif')->badge(Service::where('is_active', false)->count() ?: null)->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false)),
        ];
    }
}
