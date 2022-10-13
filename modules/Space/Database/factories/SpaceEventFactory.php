<?php

namespace Modules\Space\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Space\Entities\SpaceEventTranslation;

class SpaceEventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Space\Entities\SpaceEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(),
            'started_at' => now(),
            'ended_at' => now()->addWeek(),
        ];
    }

    public function hasDefaultTranslation()
    {
        return $this->has(SpaceEventTranslation::factory()->fallbackLocale(), 'translations');
    }
}
