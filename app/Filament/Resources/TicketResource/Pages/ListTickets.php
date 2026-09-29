<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
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

    public function getTabs(): array
    {
        $user = auth()->user();
        $count = fn (callable $scope) => $scope(Ticket::query())->count() ?: null;

        $tabs = [];

        if ($user->can('backoffice.access')) {
            $queue = fn (Builder $query) => $query->whereIn('status', ['submitted', 'verified', 'in_process']);
            $mine = fn (Builder $query) => $query->open()->where('assigned_to_id', $user->id);

            $tabs['antrian'] = Tab::make('Antrian')
                ->icon('heroicon-m-inbox')
                ->badge($count($queue))
                ->modifyQueryUsing(fn (Builder $query) => $queue($query)->reorder()->orderByRaw('estimated_completion_date asc nulls last')->orderBy('created_at'));
            $tabs['saya'] = Tab::make('Tugas Saya')
                ->icon('heroicon-m-user')
                ->badge($count($mine))
                ->modifyQueryUsing($mine);
        }

        $overdue = fn (Builder $query) => $query->overdue();
        $approval = fn (Builder $query) => $query->awaitingApproval();
        $pickup = fn (Builder $query) => $query->where('status', 'completed')->where('ready_for_pickup', true);

        $tabs['terlambat'] = Tab::make('Terlambat')
            ->icon('heroicon-m-exclamation-triangle')
            ->badge($count($overdue))
            ->badgeColor('danger')
            ->modifyQueryUsing($overdue);
        $tabs['persetujuan'] = Tab::make('Menunggu Persetujuan')
            ->icon('heroicon-m-clipboard-document-check')
            ->badge($count($approval))
            ->modifyQueryUsing($approval);
        $tabs['siap-diambil'] = Tab::make('Siap Diambil')
            ->icon('heroicon-m-hand-raised')
            ->badge($count($pickup))
            ->modifyQueryUsing($pickup);
        $tabs['semua'] = Tab::make('Semua');

        return $tabs;
    }

    public function getDefaultActiveTab(): string | int | null
    {
        $user = auth()->user();

        return match (true) {
            $user->can('backoffice.access') => 'antrian',
            $user->hasRole('front_desk') => 'siap-diambil',
            default => 'semua',
        };
    }
}
