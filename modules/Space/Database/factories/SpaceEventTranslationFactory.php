<?php

namespace Modules\Space\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class SpaceEventTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Space\Entities\SpaceEventTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'locale' => Arr::random(config('translatable.locales')),
        ];
    }

    public function fallbackLocale()
    {
        return $this->state(function (array $attributes) {
            return [
                'locale' => config('app.fallback_locale'),
            ];
        });
    }
}
