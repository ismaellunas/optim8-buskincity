<?php

namespace Modules\Ecommerce\Policies;

use App\Models\User;
use App\Policies\BasePermissionPolicy;
use Modules\Ecommerce\Entities\Order;
use Modules\Ecommerce\Enums\BookingStatus;
use Modules\Ecommerce\Enums\OrderStatus;

class OrderPolicy extends BasePermissionPolicy
{
    protected $basePermission = 'order';

    public function cancel(User $user, Order $order)
    {
        return (
            $user->can('order.edit')
            && $order->status != OrderStatus::CANCELED->value
            && $order->lines->first()->scheduleBooking->status == BookingStatus::UPCOMING->value
        );
    }
}
