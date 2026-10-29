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

        if ($this->activeTab === self::JENIS_SK_TAB) {
            return 'Jenis SK yang dipilih petugas saat meminta nomor di Register Penomoran SK. Nomor SK tetap berurutan per tahun ({N} TAHUN {YYYY}) apa pun jenisnya, terpisah dari nomor surat keluar. '
                // Who may do what here, by the active role (PersuratanMasterPolicy).
                . (PersuratanMasterResource::canDeleteAny()
                    ? 'Sebagai Administrator, Anda juga dapat menghapus jenis SK; SK yang sudah memakainya tetap menyimpan jenisnya.'
                    : 'Anda dapat menambah dan mengubah jenis SK. Menghapus hanya dari peran Administrator; untuk menyembunyikan jenis, matikan "Aktif".');
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
            ->except(self::JENIS_SK_TAB)
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
            // Jenis SK: the classifications of the separate SK register, kept apart from the surat keluar lists.
            ->put(self::JENIS_SK_TAB, Tab::make(PersuratanMaster::TYPES[self::JENIS_SK_TAB])
                ->icon('heroicon-m-document-check')
                ->badge($counts[self::JENIS_SK_TAB] ?? null)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', self::JENIS_SK_TAB)))
            ->all();
    }

    public const NUMBERING_TAB = 'penomoran';

    public const VARIABLE_TAB = 'variabel';

    public const JENIS_SK_TAB = 'jenis_sk';

    /** List type behind the active tab (the numbering tab works on jenis surat). */
    public function activeListType(): ?string
    {
        return in_array($this->activeTab, [self::NUMBERING_TAB, self::VARIABLE_TAB], true) ? 'jenis_surat' : ($this->activeTab ?: null);
    }

    protected function getHeaderActions(): array
    {
        return [
            // Jenis SK tab: how each jenis was used over a year (Rekap Register SK).
            \Filament\Actions\Action::make('skRecap')
                ->label('Rekap pemakaian')
                ->icon('heroicon-m-chart-bar')
                ->color('gray')
                ->url(fn () => \App\Filament\Pages\Reports\SkRegisterRecap::getUrl())
                ->visible(fn () => $this->activeTab === self::JENIS_SK_TAB && \App\Filament\Pages\Reports\SkRegisterRecap::canAccess()),
            CreateAction::make()
                ->label(fn () => $this->activeTab === self::JENIS_SK_TAB ? 'Tambah jenis SK' : 'Tambah pilihan')
                ->modalHeading(fn (CreateAction $action) => $this->activeTab === self::JENIS_SK_TAB ? 'Tambah jenis SK' : __('filament-actions::create.single.modal.heading', ['label' => $action->getModelLabel()]))
                ->fillForm(fn () => ['type' => $this->activeListType() ?: 'tujuan_naskah', 'is_active' => true, 'sort' => 0]),
        ];
    }
}
