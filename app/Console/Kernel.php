<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Heartbeat read by Monitoring Sistem to tell whether the scheduler runs.
        $schedule->call(fn () => \App\Services\SystemMonitorService::beat())->everyMinute()->name('scheduler-heartbeat');
    }

    /**
     * Register the commands for the application.
     */
    protected $commands = [
        \App\Console\Commands\CreateQuarterlySurveyArchives::class,
        \App\Console\Commands\CreateSampleSurveyData::class,
    ];

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}