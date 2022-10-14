<?php

namespace Modules\Booking\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Modules\Booking\Emails\EventReminder as EventReminderEmail;
use Modules\Booking\Entities\Event;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EventEmailReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'booking-event:email-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminder 30 minutes before booked event start.';

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
        $executionTime->addMinutes(30);

        $maxTime = $executionTime->copy()->addMinutes(10);

        $remindingEvents = Event::upcoming()
            ->where('booked_at', '>=', $executionTime->toDateTimeString())
            ->where('booked_at', '<', $maxTime->toDateTimeString())
            ->select('id', 'booked_at', 'status', 'order_line_id')
            ->with([
                'orderLine' => function ($query) {
                    $query->select('id', 'order_id');
                    $query->with('order.user', function ($query) {
                        $query->select('id', 'first_name', 'last_name', 'email');
                    });
                },
            ])
            ->get();

        $this->info(
            'Reminder for: '.$remindingEvents->count().' event(s)',
            OutputInterface::VERBOSITY_VERBOSE
        );

        foreach ($remindingEvents as $event) {
            Mail::to($event->orderLine->order->emailRecipient)
                ->queue(new EventReminderEmail($event->orderLine->order));
        }
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
