<?php

namespace Modules\Ecommerce\Database\factories;

use GetCandy\Database\Factories\OrderFactory as GetCandyOrderFactory;

class OrderFactory extends GetCandyOrderFactory
{
    protected $model = \Modules\Ecommerce\Entities\Order::class;

    public function definition(): array
    {
        $attributes = parent::definition();

        $attributes['sub_total'] = (int) $attributes['sub_total'];
        $attributes['tax_total'] = (int) $attributes['tax_total'];

        return $attributes;
    }
}
