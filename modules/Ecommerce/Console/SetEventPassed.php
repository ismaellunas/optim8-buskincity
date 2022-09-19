<?php

namespace Modules\Ecommerce\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Ecommerce\Entities\Event;
use Modules\Ecommerce\Enums\BookingStatus;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SetEventPassed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'booking-event:status-to-passed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the event(s) status from upcoming or ongoing to passed, once they pass the time';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $executionTime = Carbon::parse($this->option('execution-time'), $this->option('timezone'));

        $affectedStatus = [
            BookingStatus::UPCOMING,
            BookingStatus::ONGOING
        ];

        $passedNumber = Event::whereIn('status', $affectedStatus)
            ->where(
                DB::raw("booked_at + (duration || ' ' || duration_unit)::INTERVAL"),
                '<=',
                $executionTime->toDateTimeString()
            )
            ->update([
                'status' => BookingStatus::PASSED,
            ]);

        $this->info(
            'Affected Number of Events: '.$passedNumber,
            OutputInterface::VERBOSITY_VERBOSE
        );
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['execution-time', null, InputOption::VALUE_OPTIONAL, 'Execution time with format: "Y-m-d H:i", default is now("UTC").', now('UTC')->toDateTimeString()],
            ['timezone', null, InputOption::VALUE_OPTIONAL, 'PHP Time Zone.', 'UTC'],
        ];
    }
}
