<?php

namespace Modules\Booking\Database\factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Booking\Enums\CheckInType;
use Modules\Ecommerce\Entities\Order;

class OrderCheckInFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Booking\Entities\OrderCheckIn::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'checked_in_at' => now('UTC'),
            'type' => CheckInType::DISTANCE,
            'geolocation' => [
                'latitude' => 51.509865,
                'longitude' => -0.118092,
            ],
            'data' => [
                'calculated_distance_in_meters' => 0,
                'allowed_distance_in_meters' => null,
                'base_geolocation' => [
                    'latitude' => 51.509865,
                    'longitude' => -0.118092,
                ]
            ],
            'user_id' => User::factory(),
            'order_id' => Order::factory(),
        ];
    }
}
