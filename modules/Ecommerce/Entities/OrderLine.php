<?php

namespace Modules\Ecommerce\Entities;

use Lunar\Models\OrderLine as LunarOrderLine;
use Modules\Ecommerce\Database\factories\OrderLineFactory;
use Lunar\Models\Currency;

class OrderLine extends LunarOrderLine
{
    protected static function newFactory(): OrderLineFactory
    {
        return OrderLineFactory::new();
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function currency()
    {
        return $this->hasOneThrough(
            Currency::class,
            Order::class,
            'id',
            'code',
            'order_id',
            'currency_code'
        );
    }
}
