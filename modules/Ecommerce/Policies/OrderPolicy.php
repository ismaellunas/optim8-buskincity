<?php

namespace Modules\Ecommerce\Policies;

use App\Models\User;
use App\Policies\BasePermissionPolicy;
use Modules\Booking\Enums\BookingStatus;
use Modules\Ecommerce\Entities\Order;
use Modules\Ecommerce\Enums\OrderStatus;
use Illuminate\Support\Traits\Macroable;

class OrderPolicy extends BasePermissionPolicy
{
    use Macroable;

    protected $basePermission = 'order';

    public function cancel(User $user, Order $order)
    {
        return (
            $order->status != OrderStatus::CANCELED->value
            && $order->firstEventline->latestEvent->status == BookingStatus::UPCOMING->value
            && (
                $user->can('order.edit')
                || $order->isUserWhoPlacedTheOrder($user)
            )
        );
    }

    public function reschedule(User $user, Order $order)
    {
        return $this->cancel($user, $order);
    }
}
