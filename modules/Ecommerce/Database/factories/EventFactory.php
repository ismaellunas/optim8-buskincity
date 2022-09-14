<?php

namespace Modules\Ecommerce\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Ecommerce\Entities\Schedule;
use Modules\Ecommerce\Enums\BookingStatus;

class EventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Ecommerce\Entities\Event::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'schedule_id' => Schedule::factory(),
            'booked_at' => today()->addWeek()->startOfWeek()->setTime(9,0),
            'duration' => 30,
            'duration_unit' => 'minute',
            'status' => BookingStatus::UPCOMING->value,
        ];
    }
}
