<?php

namespace Modules\FormBuilder\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FormFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\FormBuilder\Entities\Form::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $title = $this->faker->words(2, true);

        return [
            'key' => Str::snake($title), // form id
            'name' => Str::title($title),
            'setting' => [
                'button' => [
                    'text' => 'Submit',
                    "position" => null,
                ],
            ],
        ];
    }
}

