<?php

namespace Modules\Ecommerce\Entities;

use GetCandy\Models\Order as GetCandyOrder;
use GetCandy\Models\OrderLine;
use Modules\Ecommerce\Enums\OrderLineType;

class Order extends GetCandyOrder
{
    public function getUserFullNameAttribute(): string|null
    {
        return $this->user->fullName ?? null;
    }

    public function getFormattedPlacedAtAttribute(): string|null
    {
        return $this->placed_at
            ? $this->placed_at->format(config('constants.format.date_time'))
            : null;
    }

    public function firstEventLine()
    {
        return $this
            ->hasOne(OrderLine::class)
            ->where('type', OrderLineType::EVENT->value);
    }
}
