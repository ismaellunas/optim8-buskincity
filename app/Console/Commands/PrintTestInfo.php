<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PrintTestInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:print-test-info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        \Log::info  ('asdf --- Hi test info ...');
        return 0;
    }
}
