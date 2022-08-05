<?php

namespace Database\Factories;

use App\Entities\Menus\UrlMenuBuilder;
use App\Models\{
    Menu,
    MenuItem,
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
        $typeUrl = (new UrlMenuBuilder())->getKey();

        return [
            'title' => "Dummy Menu",
            'type' => $typeUrl,
            'order' => 1,
            'menu_id' => Menu::factory(),
        ];
    }
}
