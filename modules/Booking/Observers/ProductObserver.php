<?php

namespace Modules\Booking\Observers;

use Modules\Ecommerce\Entities\Product;

class ProductObserver
{

    public function deleting(Product $product): void
    {
        $product->detachGallery();
    }
}