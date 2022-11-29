<?php

namespace Modules\Booking\Policies;

use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Modules\Booking\Enums\BookingStatus;
use Modules\Ecommerce\Entities\Order;
use Modules\Ecommerce\Enums\OrderStatus;

class OrderPolicyMixin
{
    public function cancelBooking()
    {
        return function (User $user, Order $order) {
            return (
                $order->status != OrderStatus::CANCELED->value
                && !$order->hasCheckIn()
                && $order->firstEventLine->latestEvent->status == BookingStatus::UPCOMING->value
                && (
                    $user->can('order.edit')
                    || $order->isPlacedByUser($user)
                )
            );
        };
    }

    public function rescheduleBooking()
    {
        return function(User $user, Order $order) {
            return $this->cancelBooking($user, $order);
        };
    }

    public function checkIn()
    {
        return function (User $user, Order $order, Carbon $currentTime = null): bool {
            $event = $order->firstEventLine->latestEvent;
            $product = $order->firstProduct;

            $productLocation = !empty($product->locations[0])
                ? collect($product->locations[0])
                : collect();

            $allowedStatues = [
                BookingStatus::UPCOMING->value,
                BookingStatus::ONGOING->value,
            ];

            if (
                !in_array($event->status, $allowedStatues)
                || empty($productLocation->get('latitude'))
                || empty($productLocation->get('longitude'))
            ) {
                return false;
            }

            if ($order->isPlacedByUser($user)) {
                $isValid = ($product->is_check_in_required && !$order->hasCheckIn());

                if ($isValid) {

                    $currentTime = $currentTime ?? now($event->schedule->timezone);

                    $minutes = Setting::key('allowed_early_check_in')->value('value') ?? 0;

                    $allowedTime = $event->timezonedBookedAt->subMinutes($minutes);

                    $isValid = (
                        $currentTime->gte($allowedTime)
                        && $currentTime->lt($event->endedTime)
                    );
                }

                return $isValid;
            }

            return false;
        };
    }
}
