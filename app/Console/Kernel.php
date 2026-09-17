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
        // Send booking reminders every day at 9:00 AM
        $schedule->command('booking:send-reminders')->dailyAt('09:00');

        // Remind customers with an outstanding balance as their trip approaches
        $schedule->command('booking:send-balance-reminders')->dailyAt('09:15');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
