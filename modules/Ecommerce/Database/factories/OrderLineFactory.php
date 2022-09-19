<?php

namespace Modules\Ecommerce\Database\factories;

use GetCandy\Database\Factories\OrderLineFactory as GetCandyOrderLineFactory;

class OrderLineFactory extends GetCandyOrderLineFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Ecommerce\Entities\OrderLine::class;
}
