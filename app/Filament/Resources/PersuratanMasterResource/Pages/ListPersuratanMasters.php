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
        if ($this->activeTab === self::NUMBERING_TAB) {
            return 'Format nomor surat keluar per jenis surat. Format di sini satu-satunya acuan penomoran: nomor urut, singkatan unit kerja, klasifikasi, bulan, dan tahun dirangkai otomatis saat petugas meminta nomor.';
        }

        if ($this->activeTab === self::VARIABLE_TAB) {
            return 'Keterangan tambahan per jenis surat, mis. kelas atau nama kegiatan. Petugas wajib mengisinya saat meminta nomor; nilainya masuk ke nomor lewat token {v} (seperti diketik) atau {V} (huruf besar).';
        }

        return 'Pilihan rutin pada form surat keluar dan disposisi. Isian yang diketik petugas tercatat di sini sebagai pilihan nonaktif; aktifkan bila ingin dijadikan pilihan rutin.';
    }

    public function getTabs(): array
    {
        $counts = PersuratanMaster::query()->selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type');

        return collect(PersuratanMaster::TYPES)
            ->mapWithKeys(fn (string $label, string $type) => [$type => Tab::make($label)
                ->badge($counts[$type] ?? null)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', $type))])
            // Penomoran Otomatis: the format of each letter type (jenis surat), one place for all numbering.
            ->put(self::NUMBERING_TAB, Tab::make('Penomoran Otomatis')
                ->icon('heroicon-m-hashtag')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'jenis_surat')))
            // Variabel Tambahan: the extra value ({v}/{V}) each letter type asks for when a number is requested.
            ->put(self::VARIABLE_TAB, Tab::make('Variabel Tambahan')
                ->icon('heroicon-m-variable')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'jenis_surat')))
            ->all();
    }

    public const NUMBERING_TAB = 'penomoran';

    public const VARIABLE_TAB = 'variabel';

    /** List type behind the active tab (the numbering tab works on jenis surat). */
    public function activeListType(): ?string
    {
        return in_array($this->activeTab, [self::NUMBERING_TAB, self::VARIABLE_TAB], true) ? 'jenis_surat' : ($this->activeTab ?: null);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah pilihan')
                ->fillForm(fn () => ['type' => $this->activeListType() ?: 'tujuan_naskah', 'is_active' => true, 'sort' => 0]),
        ];
    }
}
