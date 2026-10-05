<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;

/** Keeps the in-app notifications table small: read ones after 90 days, any after 180. */
class PruneNotifications extends Command
{
    public const READ_DAYS = 90;

    public const ALL_DAYS = 180;

    protected $signature = 'notifications:prune';

    protected $description = 'Hapus notifikasi in-app lama (dibaca > 90 hari, semua > 180 hari)';

    public function handle(): int
    {
        $read = DatabaseNotification::whereNotNull('read_at')->where('read_at', '<', now()->subDays(self::READ_DAYS))->delete();
        $old = DatabaseNotification::where('created_at', '<', now()->subDays(self::ALL_DAYS))->delete();

        $this->info("{$read} notifikasi dibaca dan {$old} notifikasi lama dihapus.");

        return self::SUCCESS;
    }
}
