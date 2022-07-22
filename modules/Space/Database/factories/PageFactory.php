<?php
namespace Modules\Space\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Space\Entities\Page;

class PageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Page::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'type' => Page::TYPE,
        ];
    }
}
