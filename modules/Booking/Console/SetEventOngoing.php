<?php

namespace Modules\Booking\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Booking\Entities\Event;
use Modules\Booking\Entities\Schedule;
use Modules\Booking\Enums\BookingStatus;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SetEventOngoing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'booking-event:status-to-ongoing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the event(s) status from upcoming to ongoing, once they enter the booked time';

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

        if ($this->option('timezone') != 'UTC') {
            $executionTime->setTimezone('UTC');
        }

        $executionTime->addMinutes(2);

        $minTime = $executionTime->copy()->subHours(12);

        $eventTable = Event::getTableName();
        $scheduleTable = Schedule::getTableName();

        $timezoneSubQuery = (
            "SELECT {$scheduleTable}.timezone ".
            "FROM {$scheduleTable} ".
            "WHERE {$scheduleTable}.id = {$eventTable}.schedule_id"
        );

        $updatedNumber = Event::upcoming()
            ->whereRaw(
                "(({$eventTable}.booked_at AT TIME ZONE ($timezoneSubQuery)) AT TIME ZONE 'UTC') <= ?",
                [$executionTime->toDateTimeString()]
            )
            ->whereRaw(
                "(({$eventTable}.booked_at AT TIME ZONE ($timezoneSubQuery)) AT TIME ZONE 'UTC') > ?",
                [$minTime->toDateTimeString()]
            )
            ->update([
                'status' => BookingStatus::ONGOING,
            ]);

        $this->info(
            "Affected Number of Events: ".$updatedNumber,
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
