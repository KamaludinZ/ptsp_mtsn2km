<?php

namespace App\Filament\Resources\NotificationDeliveryResource\Pages;

use App\Filament\Resources\NotificationDeliveryResource;
use App\Models\NotificationDelivery;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListNotificationDeliveries extends ListRecords
{
    protected static string $resource = NotificationDeliveryResource::class;

    public function getSubheading(): ?string
    {
        $today = NotificationDelivery::whereDate('created_at', today());

        return 'Hari ini: ' . (clone $today)->where('status', 'sent')->count() . ' terkirim, ' . (clone $today)->where('status', 'failed')->count() . ' gagal.';
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua'),
            'gagal' => Tab::make('Gagal')
                ->badge(NotificationDelivery::where('status', 'failed')->count() ?: null)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'failed')),
            'terkirim' => Tab::make('Terkirim')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'sent')),
        ];
    }
}
