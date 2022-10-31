<?php

namespace Modules\Booking\Policies;

use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Modules\Booking\Enums\BookingStatus;
use Modules\Ecommerce\Entities\Order;

class OrderPolicyMixin
{
    public function checkIn()
    {
        return function (User $user, Order $order, Carbon $currentTime = null): bool {
            $event = $order->firstEventLine->latestEvent;
            $product = $order->firstProduct;

            $productLocation = collect($product->locations[0])->only(['latitude', 'longitude']);

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

            if ($order->user_id == $user->id) {
                $isValid = ($product->is_check_in_required && $order->hasAllowedCheckIn());

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
