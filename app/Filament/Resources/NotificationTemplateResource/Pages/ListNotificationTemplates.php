<?php

namespace App\Filament\Resources\NotificationTemplateResource\Pages;

use App\Filament\Resources\NotificationTemplateResource;
use Filament\Resources\Pages\ListRecords;

class ListNotificationTemplates extends ListRecords
{
    protected static string $resource = NotificationTemplateResource::class;

    public function getSubheading(): ?string
    {
        return 'Isi pesan setiap notifikasi per kanal. Pesan hanya terkirim bila templatnya dan kanalnya aktif.';
    }
}
