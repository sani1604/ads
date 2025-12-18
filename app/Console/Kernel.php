<?php
// app/Console/Kernel.php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Process expiring subscriptions daily at 8 AM
        $schedule->command('subscriptions:process-expiring')
            ->dailyAt('08:00')
            ->withoutOverlapping();

        // Generate recurring invoices daily at 6 AM
        $schedule->command('invoices:generate-recurring')
            ->dailyAt('06:00')
            ->withoutOverlapping();

        // Cleanup old data weekly on Sunday at 2 AM
        $schedule->command('cleanup:old-data --days=90')
            ->weeklyOn(0, '02:00')
            ->withoutOverlapping();

        // Clear expired password reset tokens daily
        $schedule->command('auth:clear-resets')
            ->daily();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}