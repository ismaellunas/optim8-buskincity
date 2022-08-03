<?php
namespace Modules\Event\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Event\Entities\EventTranslation;

class EventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Event\Entities\Event::class;

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
        return $this->has(EventTranslation::factory()->fallbackLocale(), 'translations');
    }
}
