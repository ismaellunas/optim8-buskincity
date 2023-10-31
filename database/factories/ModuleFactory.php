<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Module>
 */
class ModuleFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => Str::snake(fake()->unique()->words(2, true)),
            'title' => fake()->name(),
            'is_active' => fake()->boolean(),
            'is_manageable' => fake()->boolean(),
            'order' => 0,
        ];
    }

    public function activated(bool $isActive = true)
    {
        return $this->state(function (array $attributes) use ($isActive) {
            return [
                'is_active' => $isActive,
            ];
        });
    }
}
