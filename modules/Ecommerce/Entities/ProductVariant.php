<?php

namespace Modules\Ecommerce\Entities;

use GetCandy\Models\ProductVariant as GetCandyProductVariant;
use Modules\Ecommerce\Entities\Product;

class ProductVariant extends GetCandyProductVariant
{
    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
