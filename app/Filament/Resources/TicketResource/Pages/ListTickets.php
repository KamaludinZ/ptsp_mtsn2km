<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Support\TicketSearch;
use App\Support\TicketTabs;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    public function getSubheading(): ?string
    {
        return 'Permohonan online dan dari loket dalam satu daftar. Buka tiket untuk memproses.';
    }

    /** One search box over number, applicant, WhatsApp, service, officer and request text. */
    protected function applyGlobalSearchToTableQuery(Builder $query): Builder
    {
        return TicketSearch::apply($query, $this->getTableSearch());
    }

    public function getTabs(): array
    {
        $user = auth()->user();
        $counts = TicketTabs::counts($user);
        $icons = [
            'antrian' => 'heroicon-m-inbox',
            'saya' => 'heroicon-m-user',
            'disposisi-unit' => 'heroicon-m-arrow-right-circle',
            'terlambat' => 'heroicon-m-exclamation-triangle',
            'persetujuan' => 'heroicon-m-clipboard-document-check',
            'siap-diambil' => 'heroicon-m-hand-raised',
        ];

        return collect(TicketTabs::for($user))->mapWithKeys(fn (string $tab) => [$tab => Tab::make(TicketTabs::LABELS[$tab])
            ->icon($icons[$tab] ?? null)
            ->badge($tab === 'semua' ? null : ($counts[$tab] ?: null))
            ->badgeColor(match ($tab) {
                'terlambat' => 'danger',
                'disposisi-unit' => 'info',
                default => null,
            })
            ->modifyQueryUsing(fn (Builder $query) => TicketTabs::apply($query, $tab, $user, ordered: true)),
        ])->all();
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return TicketTabs::default(auth()->user());
    }
}
