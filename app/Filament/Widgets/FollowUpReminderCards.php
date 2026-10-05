<?php

namespace App\Filament\Widgets;

use App\Support\FollowUpReminders;
use Filament\Widgets\Widget;

/** Kartu pengingat tindak lanjut for the signed-in officer (Notifikasi page). */
class FollowUpReminderCards extends Widget
{
    protected static string $view = 'filament.widgets.follow-up-reminder-cards';

    protected int | string | array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    protected static bool $isLazy = false;

    public function getReminders(): \Illuminate\Support\Collection
    {
        return FollowUpReminders::for(auth()->user());
    }

    public static function canView(): bool
    {
        return (bool) auth()->user()?->isStaff();
    }
}
