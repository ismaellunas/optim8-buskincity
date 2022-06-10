<?php

namespace Database\Factories;

use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LanguageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Language::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $locale = $this->faker->locale();

        return [
            'name' => $this->faker->word(),
            'code' => Str::before($locale, '_'),
            'locale' => $locale,
        ];
    }

    public function englishUS()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'English',
                'code' => 'en',
                'locale' => 'en_US',
            ];
        });
    }
}
