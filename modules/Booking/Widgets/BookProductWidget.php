<?php

namespace Modules\Booking\Widgets;

use App\Contracts\WidgetInterface;
use App\Entities\Widgets\DefaultWidget;
use Modules\Ecommerce\Entities\Order;

class BookProductWidget extends DefaultWidget implements WidgetInterface
{
    public function canBeAccessed(): bool
    {
        return parent::canBeAccessed()
            && $this->user->can('showFrontendOrder', Order::class);
    }
}
