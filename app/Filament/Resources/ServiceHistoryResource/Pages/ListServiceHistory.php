<?php

namespace App\Filament\Resources\ServiceHistoryResource\Pages;

use App\Exports\ServiceHistoryExport;
use App\Filament\Resources\ServiceHistoryResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListServiceHistory extends ListRecords
{
    protected static string $resource = ServiceHistoryResource::class;

    public function getSubheading(): ?string
    {
        return 'Jejak setiap tahap permohonan: pelaku, waktu, perubahan status, dan catatan. Riwayat tidak dapat diubah atau dihapus.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Ekspor Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->tooltip('Unduh riwayat sesuai pencarian & filter yang aktif')
                ->action(function () {
                    $query = $this->getFilteredSortedTableQuery();

                    activity('audit')
                        ->causedBy(auth()->user())
                        ->withProperties(['rows' => (clone $query)->count(), 'filters' => $this->tableFilters, 'search' => $this->tableSearch])
                        ->log('Mengekspor riwayat layanan');

                    return Excel::download(new ServiceHistoryExport($query), 'riwayat-layanan-' . now()->format('Ymd-His') . '.xlsx');
                }),
        ];
    }
}
