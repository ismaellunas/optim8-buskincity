<?php

namespace Modules\Booking\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ReplaceEventCalendarsView extends Command
{
    protected $signature = 'replace-view:event-calendars';

    protected $description = 'Replace event_calendars view';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $migration = '2023_09_13_073640_create_event_calendars_view';

        DB::table('migrations')
            ->where('migration', $migration)
            ->delete();

        Artisan::call('migrate', [
            '--path' => 'database/migrations/'.$migration.'.php',
        ]);

        return Command::SUCCESS;
    }
}
