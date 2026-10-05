<?php

namespace App\Filament\Resources\PersuratanMasterResource\Pages;

use App\Filament\Resources\PersuratanMasterResource;
use App\Models\PersuratanMaster;
use Filament\Actions\CreateAction;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPersuratanMasters extends ListRecords
{
    protected static string $resource = PersuratanMasterResource::class;

    public function getSubheading(): ?string
    {
        return 'Pilihan rutin pada form surat keluar dan disposisi. Isian yang diketik petugas tercatat di sini sebagai pilihan nonaktif; aktifkan bila ingin dijadikan pilihan rutin.';
    }

    public function getTabs(): array
    {
        $counts = PersuratanMaster::query()->selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type');

        return collect(PersuratanMaster::TYPES)
            ->mapWithKeys(fn (string $label, string $type) => [$type => Tab::make($label)
                ->badge($counts[$type] ?? null)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', $type))])
            ->all();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah pilihan')
                ->fillForm(fn () => ['type' => $this->activeTab ?: 'tujuan_naskah', 'is_active' => true, 'sort' => 0]),
        ];
    }
}
