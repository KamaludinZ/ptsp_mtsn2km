<?php

use Illuminate\Foundation\Inspiring;
use App\Services\SystemMonitorService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Monitoring Sistem: heartbeat (is the scheduler running?) and health history.
Schedule::call(fn () => SystemMonitorService::beat())->everyMinute()->name('scheduler-heartbeat');
// Every five minutes: health snapshot, pruning, redeploy detection, critical alert (monitor:collect).
Schedule::command('monitor:collect')->everyFiveMinutes()->name('monitor-record')->withoutOverlapping(10);

// Laporan SKM & SPAK: archive the quarter that just ended (first day of each quarter).
Schedule::call(function () {
    $previous = now()->subQuarter();
    foreach (['skm', 'spak'] as $type) {
        Artisan::call('app:create-quarterly-survey-archives', ['--type' => $type, '--year' => $previous->year, '--quarter' => 'Q' . $previous->quarter]);
    }
})->quarterlyOn(1, '01:00')->name('survey-quarterly-archive');

// Notifikasi in-app: drop old notifications every night.
Schedule::command('notifications:prune')->dailyAt('02:30')->name('notifications-prune');

// Pengingat survei: applicants who have not rated a finished request (day 1 and day 7), every morning.
Schedule::command('surveys:remind')->dailyAt('08:00')->name('surveys-remind')->withoutOverlapping();

// Pengingat permohonan tertunda: every working day at 07:00.
Schedule::command('tickets:remind-pending')->weekdays()->dailyAt('07:00')->name('tickets-remind-pending')->withoutOverlapping();
