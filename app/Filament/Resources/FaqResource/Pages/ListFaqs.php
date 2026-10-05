<?php

namespace App\Filament\Resources\FaqResource\Pages;

use App\Filament\Resources\FaqResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFaqs extends ListRecords
{
    protected static string $resource = FaqResource::class;

    public function getSubheading(): ?string
    {
        return 'Pertanyaan yang sering diajukan, tampil di halaman FAQ dan beranda. Seret baris untuk mengubah urutan.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah FAQ')->icon('heroicon-m-plus'),
        ];
    }
}
