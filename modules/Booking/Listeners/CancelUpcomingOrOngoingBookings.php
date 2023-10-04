<?php

namespace Modules\Booking\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Booking\Entities\Event;
use Modules\Booking\Events\ModuleDeactivated;
use Modules\Ecommerce\Services\OrderService;

class CancelUpcomingOrOngoingBookings implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        protected OrderService $orderService
    ) {}

    public function handle(ModuleDeactivated $event)
    {
        Event::ongoing()
            ->orWhere(fn ($query) => $query->upcoming())
            ->get()
            ->each(function ($event) {
                if ($event->order) {
                    $this->orderService->cancelOrder($event->order);
                }

                $this->orderService->cancelEvent(
                    $event,
                    "Module is deactivated");
            });
    }
}
