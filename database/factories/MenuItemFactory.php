<?php

namespace Database\Factories;

use App\Models\{
    Menu,
    MenuItem,
    Page,
};
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MenuItem::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'type' => MenuItem::TYPE_PAGE,
            'order' => 1,
            'menu_id' => Menu::factory(),
            'page_id' => Page::factory()->hasTranslations(1),
        ];
    }
}
