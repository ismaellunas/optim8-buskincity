<?php

namespace Modules\Ecommerce\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    protected $model = \Modules\Ecommerce\Entities\Schedule::class;

    public function definition()
    {
        return [
            'timezone' => 'UTC',
        ];
    }
}
