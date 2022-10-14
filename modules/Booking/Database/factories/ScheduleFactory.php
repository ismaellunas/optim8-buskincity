<?php

namespace Modules\Booking\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    protected $model = \Modules\Booking\Entities\Schedule::class;

    public function definition()
    {
        return [
            'timezone' => 'UTC',
        ];
    }
}
