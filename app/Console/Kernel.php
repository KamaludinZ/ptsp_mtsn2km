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
        // Schedules live in routes/console.php (Laravel 11+ does not load this kernel).
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