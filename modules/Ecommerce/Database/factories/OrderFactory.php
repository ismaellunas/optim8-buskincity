<?php

namespace Modules\Ecommerce\Database\factories;

use Lunar\Database\Factories\OrderFactory as LunarOrderFactory;

class OrderFactory extends LunarOrderFactory
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
