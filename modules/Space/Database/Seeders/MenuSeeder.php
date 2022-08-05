<?php

namespace Modules\Space\Database\Seeders;

use App\Entities\Menus\UrlMenuBuilder;
use App\Entities\Caches\MenuCache;
use App\Models\{
    Menu,
    MenuItem,
};
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menu = Menu::header()->locale(config('app.fallback_locale'))->first();

        $headerMenus = [
            [
                'title' => 'Country',
                'type' => (new UrlMenuBuilder())->getKey(),
                'url' => route('frontend.spaces.index'),
                'order' => 99,
                'menu_id' => $menu->id
            ]
        ];

        MenuItem::factory()
            ->count(1)
            ->state(new Sequence(...$headerMenus))
            ->create();

        app(MenuCache::class)->flush();
    }
}
