<?php

namespace Modules\FormBuilder\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\FormBuilder\Entities\Form;
use Modules\FormBuilder\Entities\FormMappingRule;

class FormMappingRuleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FormMappingRule::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'form_id' => Form::factory(),
            'type' => fake()->word(),
            'key' => 'form',
            'to' => ['name' => fake()->word],
        ];
    }
}
