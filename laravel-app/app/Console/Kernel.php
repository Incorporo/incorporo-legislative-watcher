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
        // Run batch AI assessment daily at 2 AM (low-traffic time)
        // Assesses top 10 priority bills per day to manage costs
        $schedule->command('bills:auto-assess --limit=10 --priority=50')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->onOneServer()
            ->emailOutputOnFailure(config('mail.admin_email'));

        // Weekly comprehensive assessment (Sundays at 3 AM)
        // Assesses more bills with lower priority threshold
        $schedule->command('bills:auto-assess --limit=50 --priority=30')
            ->weeklyOn(0, '03:00')
            ->withoutOverlapping()
            ->onOneServer();

        // Retry failed assessments (daily at 4 AM)
        $schedule->command('bills:auto-assess --limit=5 --priority=40')
            ->dailyAt('04:00')
            ->withoutOverlapping()
            ->onOneServer();
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
