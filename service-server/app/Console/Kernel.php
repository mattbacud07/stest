<?php

namespace App\Console;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
//         Locate your php.ini file. This is typically located in your PHP installation directory.
//          Set the date.timezone:
        // $schedule->command('app:PMUpdateStatus')->twiceDaily(8, 13);
        // $schedule->command('app:PMUpdateStatus')->dailyAt('08:00');
        // $schedule->command('app:PMUpdateStatus')->everyFiveSeconds();

        // $startOfMonitoringTime = '08:00';
        $startOfMonitoringTime = '8';
        $anotherMonitoringTime = '13';
        // $schedule->command('app:monitor-pm-cm-after-service')->everyMinute();
        // $schedule->command('app:update-pm-status-to-ready')->everyMinute();
        $schedule->command('app:monitor-pm-cm-after-service')->twiceDaily($startOfMonitoringTime, $anotherMonitoringTime);
        $schedule->command('app:update-pm-status-to-ready')->twiceDaily($startOfMonitoringTime, $anotherMonitoringTime);
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
