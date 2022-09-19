<?php

namespace Modules\Ecommerce\Entities;

use GetCandy\Models\OrderLine as GetCandyOrderLine;
use Modules\Ecommerce\Database\factories\OrderLineFactory;
use GetCandy\Models\Currency;

class OrderLine extends GetCandyOrderLine
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

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function latestEvent()
    {
        return $this->hasOne(Event::class)->latest();
    }
}
