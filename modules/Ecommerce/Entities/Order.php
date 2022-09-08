<?php

namespace Modules\Ecommerce\Entities;

use GetCandy\Models\Order as GetCandyOrder;
use GetCandy\Models\OrderLine;
use Modules\Ecommerce\Enums\OrderLineType;
use Modules\Ecommerce\Database\factories\OrderFactory;

class Order extends GetCandyOrder
{
    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\OrderFactory
     */
    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }

    public function getUserFullNameAttribute(): ?string
    {
        return $this->user->fullName ?? null;
    }

    public function getFormattedPlacedAtAttribute(): ?string
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
