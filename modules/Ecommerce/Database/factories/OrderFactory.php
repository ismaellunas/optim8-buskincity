<?php

namespace Modules\Ecommerce\Database\factories;

use GetCandy\Database\Factories\OrderFactory as GetCandyOrderFactory;

class OrderFactory extends GetCandyOrderFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Ecommerce\Entities\Order::class;
}
