<?php

namespace App\Console;

use App\Console\Commands\AddWebsite;
use App\Console\Commands\RunHealthChecks;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        RunHealthChecks::class,
        AddWebsite::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(RunHealthChecks::class)->everyFifteenMinutes();
    }
}
