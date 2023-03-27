<?php

namespace Modules\Ecommerce\Database\factories;

use Lunar\Database\Factories\OrderLineFactory as LunarOrderLineFactory;

class OrderLineFactory extends LunarOrderLineFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Ecommerce\Entities\OrderLine::class;
}
