<?php

namespace App\Filament\Resources\PengumumanResource\Pages;

use App\Filament\Resources\PengumumanResource;
use App\Models\Pengumuman;
use App\Support\ContentStatus;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPengumumen extends ListRecords
{
    protected static string $resource = PengumumanResource::class;

    public function getSubheading(): ?string
    {
        return 'Pengumuman tayang di situs publik antara tanggal tayang dan tanggal berakhirnya.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Buat pengumuman')->icon('heroicon-m-plus'),
        ];
    }

    public function getTabs(): array
    {
        $counts = ContentStatus::counts();

        return ['semua' => Tab::make('Semua')->badge(Pengumuman::count())]
            + collect(ContentStatus::LABELS)->mapWithKeys(fn (string $label, string $status) => [$status => Tab::make($label)
                ->badge($counts[$status] ?: null)
                ->badgeColor(ContentStatus::COLORS[$status])
                ->modifyQueryUsing(fn (Builder $query) => ContentStatus::scope($query, $status))])->all();
    }
}
