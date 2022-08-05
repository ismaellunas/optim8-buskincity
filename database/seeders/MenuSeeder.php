<?php

namespace Database\Seeders;

use App\Entities\Caches\MenuCache;
use App\Entities\Menus\SegmentMenuBuilder;
use App\Entities\Menus\UrlMenuBuilder;
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
        $typeUrl = (new UrlMenuBuilder())->getKey();
        $typeSegment = (new SegmentMenuBuilder())->getKey();

        $headerMenus = [
            [
                'title' => 'Home',
                'type' => $typeUrl,
                'url' => route('homepage'),
                'order' => 1,
            ],
            [
                'title' => 'Street Performers',
                'type' => $typeUrl,
                'url' => '#',
                'order' => 2,
            ],
            [
                'title' => 'Blog',
                'type' => $typeUrl,
                'url' => route('blog.index'),
                'order' => 3,
            ],
            [
                'title' => 'About',
                'type' => $typeUrl,
                'url' => '#',
                'order' => 4,
            ],
        ];

        // Header
        Menu::factory()
            ->has(
                MenuItem::factory()
                    ->count(4)
                    ->state(new Sequence(...$headerMenus))
            )
            ->state(new Sequence(
                ['type' => Menu::TYPE_HEADER],
            ))
            ->create([
                'locale' => config('app.fallback_locale'),
            ]);

        $footerMenus = [
            [
                'title' => 'About',
                'type' => $typeSegment,
                'order' => 1,
            ],
            [
                'title' => 'Performers',
                'type' => $typeSegment,
                'order' => 2,
            ],
            [
                'title' => 'General',
                'type' => $typeSegment,
                'order' => 3,
            ],
        ];

        // Footer
        $menu = Menu::factory()
            ->has(
                MenuItem::factory()
                    ->count(3)
                    ->state(new Sequence(...$footerMenus))
            )
            ->state(new Sequence(
                ['type' => Menu::TYPE_FOOTER],
            ))
            ->create([
                'locale' => config('app.fallback_locale'),
            ]);

        $footerMenus = [
            [
                'title' => 'Blog',
                'type' => $typeUrl,
                'url' => route('blog.index'),
                'order' => 1,
                'parent_id' => $menu->menuItems[0]->id,
                'menu_id' => $menu->id,
            ],
            [
                'title' => 'About',
                'type' => $typeUrl,
                'url' => '#',
                'order' => 2,
                'parent_id' => $menu->menuItems[0]->id,
                'menu_id' => $menu->id,
            ],
            [
                'title' => 'Contact us',
                'type' => $typeUrl,
                'url' => '#',
                'order' => 3,
                'parent_id' => $menu->menuItems[0]->id,
                'menu_id' => $menu->id,
            ],
            [
                'title' => 'Street Performer',
                'type' => $typeUrl,
                'url' => '#',
                'order' => 1,
                'parent_id' => $menu->menuItems[1]->id,
                'menu_id' => $menu->id,
            ],
            [
                'title' => 'Become a Performer',
                'type' => $typeUrl,
                'url' => '#',
                'order' => 2,
                'parent_id' => $menu->menuItems[1]->id,
                'menu_id' => $menu->id,
            ],
            [
                'title' => 'Terms & Conditions',
                'type' => $typeUrl,
                'url' => '#',
                'order' => 1,
                'parent_id' => $menu->menuItems[2]->id,
                'menu_id' => $menu->id,
            ],
            [
                'title' => 'Privacy Policy',
                'type' => $typeUrl,
                'url' => '#',
                'order' => 2,
                'parent_id' => $menu->menuItems[2]->id,
                'menu_id' => $menu->id,
            ],
            [
                'title' => 'Cookie Policy',
                'type' => $typeUrl,
                'url' => '#',
                'order' => 3,
                'parent_id' => $menu->menuItems[2]->id,
                'menu_id' => $menu->id,
            ],
        ];

        // Footer
        MenuItem::factory()
            ->count(8)
            ->state(new Sequence(...$footerMenus))
            ->create();

        $socialMedias = [
            [
                'title' => 'Facebook',
                'type' => $typeUrl,
                'order' => 1,
                'url' => 'https://www.facebook.com/',
                'is_blank' => true,
                'icon' => 'fab fa-facebook',
            ],
            [
                'title' => 'Twitter',
                'type' => $typeUrl,
                'order' => 2,
                'url' => 'https://twitter.com/',
                'is_blank' => true,
                'icon' => 'fab fa-twitter',
            ],
        ];

        // Social Media
        Menu::factory()
            ->has(
                MenuItem::factory()
                    ->count(2)
                    ->state(new Sequence(...$socialMedias))
            )
            ->create([
                'locale' => config('app.fallback_locale'),
                'type' => Menu::TYPE_SOCIAL_MEDIA
            ]);

        app(MenuCache::class)->flush();
    }
}
