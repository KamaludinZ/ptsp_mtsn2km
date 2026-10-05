<?php

namespace App\Filament\Portal\Pages;

use App\Filament\Concerns\NotificationInbox;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

/** Notifikasi for applicants (/portal/notifikasi). */
class Notifications extends Page implements HasTable
{
    use InteractsWithTable, NotificationInbox {
        NotificationInbox::table insteadof InteractsWithTable;
    }

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?string $title = 'Notifikasi';

    protected static ?string $slug = 'notifikasi';

    protected static ?int $navigationSort = 90;

    protected static string $view = 'filament.pages.notification-inbox';
}
