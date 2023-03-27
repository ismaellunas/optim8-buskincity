<?php

namespace Modules\Ecommerce\Entities;

use Lunar\Models\ProductVariant as LunarProductVariant;

class ProductVariant extends LunarProductVariant
{
    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function orderLine()
    {
        return $this->morphOne(OrderLine::class, 'purchasable');
    }
}
