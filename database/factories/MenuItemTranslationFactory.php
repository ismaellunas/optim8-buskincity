<?php

namespace Database\Factories;

use App\Models\{
    MenuItem,
    MenuItemTranslation,
};
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuItemTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MenuItemTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'locale' => config('app.fallback_locale'),
            'title' => "Dummy Menu",
            'menu_item_id' => MenuItem::factory(),
        ];
    }
}
