<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'city_id' => City::factory(),
            'name' => $this->faker->unique()->streetName(),
            'address' => $this->faker->streetAddress(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'space_id' => null,
        ];
    }
}
