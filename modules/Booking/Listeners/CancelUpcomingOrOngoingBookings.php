<?php

namespace Modules\Booking\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Modules\Booking\Entities\Event;
use Modules\Booking\Events\ModuleDeactivated;
use Modules\Ecommerce\Services\OrderService;

class CancelUpcomingOrOngoingBookings implements ShouldQueue
{
    use InteractsWithQueue;

    private $chunk = 200;

    public function __construct(
        protected OrderService $orderService
    ) {}

    public function handle(ModuleDeactivated $event)
    {
        Event::ongoing()
            ->orWhere(fn ($query) => $query->upcoming())
            ->chunk($this->chunk, function (Collection $events) {
                $events->load('order');

                $events->each(function ($event) {
                    if ($event->order) {
                        $this->orderService->cancelOrder($event->order);
                    }

                    $this->orderService->cancelEvent(
                        $event,
                        "Module is deactivated"
                    );
                });
            });
    }
}
