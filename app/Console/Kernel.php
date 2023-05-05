<?php

namespace App\Console;

use App\Events\ErrorReport;
use App\Jobs\RemoveNotVerifiedUser;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\App;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        if (config('telescope.enabled')) {
            if (App::environment('local')) {
                $schedule->command('telescope:prune --hours=48')->daily();
            } else {
                $schedule->command('telescope:prune --hours=336')->daily();
            }
        }

        if (config('constants.error_reporting')) {
            $schedule->call(function () {
                ErrorReport::dispatch();
            })->dailyAt('23:59');
        }

        $schedule->job(new RemoveNotVerifiedUser())->daily();
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
