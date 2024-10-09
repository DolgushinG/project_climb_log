<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('pulse:prune')->daily();
        $schedule->command('queue:work --stop-when-empty')->everyMinute()->withoutOverlapping();
        // $schedule->command('queue:work')->everyMinute()->withoutOverlapping();
        $schedule->command('backup:clean')->daily()->at('01:00');
        $schedule->command('backup:run --only-db')->daily()->at('02:00');
        $schedule->command('backup:monitor')->daily()->at('03:00');
        $schedule->command('events:update-status')->hourly();
        $schedule->command('participant:update-reg-status')->hourly();
        $schedule->command('sets:update-sets-participant')->everyTenMinutes();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
