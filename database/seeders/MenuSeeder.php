<?php

namespace Database\Seeders;

use App\Entities\Caches\MenuCache;
use App\Models\{
    Menu,
    MenuItem,
    Page,
    Post,
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
        $headerMenus = [
            [
                'title' => 'Home',
                'type' => MenuItem::TYPE_URL,
                'url' => route('homepage'),
                'order' => 1,
            ],
            [
                'title' => 'Street Performers',
                'type' => MenuItem::TYPE_URL,
                'url' => '#',
                'order' => 2,
            ],
            [
                'title' => 'Blog',
                'type' => MenuItem::TYPE_URL,
                'url' => route('blog.index'),
                'order' => 3,
            ],
            [
                'title' => 'About',
                'type' => MenuItem::TYPE_URL,
                'url' => '#',
                'order' => 4,
            ],
        ];

        // Header
        Menu::factory()
            ->has(
                MenuItem::factory()
                    ->count(4)
                    ->state(new Sequence(
                        $headerMenus[0],
                        $headerMenus[1],
                        $headerMenus[2],
                        $headerMenus[3],
                    ))
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
                'type' => MenuItem::TYPE_SEGMENT,
                'order' => 1,
            ],
            [
                'title' => 'Performers',
                'type' => MenuItem::TYPE_SEGMENT,
                'order' => 2,
            ],
            [
                'title' => 'General',
                'type' => MenuItem::TYPE_SEGMENT,
                'order' => 3,
            ],
        ];

        // Footer
        $menu = Menu::factory()
            ->has(
                MenuItem::factory()
                    ->count(3)
                    ->state(new Sequence(
                        $footerMenus[0],
                        $footerMenus[1],
                        $footerMenus[2],
                    ))
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
                'type' => MenuItem::TYPE_URL,
                'url' => route('blog.index'),
                'order' => 1,
                'parent_id' => $menu->menuItems[0]->id,
                'menu_id' => $menu->id,
            ],
            [
                'title' => 'About',
                'type' => MenuItem::TYPE_URL,
                'url' => '#',
                'order' => 2,
                'parent_id' => $menu->menuItems[0]->id,
                'menu_id' => $menu->id,
            ],
            [
                'title' => 'Contact us',
                'type' => MenuItem::TYPE_URL,
                'url' => '#',
                'order' => 3,
                'parent_id' => $menu->menuItems[0]->id,
                'menu_id' => $menu->id,
            ],
            [
                'title' => 'Street Performer',
                'type' => MenuItem::TYPE_URL,
                'url' => '#',
                'order' => 1,
                'parent_id' => $menu->menuItems[1]->id,
                'menu_id' => $menu->id,
            ],
            [
                'title' => 'Become a Performer',
                'type' => MenuItem::TYPE_URL,
                'url' => '#',
                'order' => 2,
                'parent_id' => $menu->menuItems[1]->id,
                'menu_id' => $menu->id,
            ],
            [
                'title' => 'Terms & Conditions',
                'type' => MenuItem::TYPE_URL,
                'url' => '#',
                'order' => 1,
                'parent_id' => $menu->menuItems[2]->id,
                'menu_id' => $menu->id,
            ],
            [
                'title' => 'Privacy Policy',
                'type' => MenuItem::TYPE_URL,
                'url' => '#',
                'order' => 2,
                'parent_id' => $menu->menuItems[2]->id,
                'menu_id' => $menu->id,
            ],
            [
                'title' => 'Cookie Policy',
                'type' => MenuItem::TYPE_URL,
                'url' => '#',
                'order' => 3,
                'parent_id' => $menu->menuItems[2]->id,
                'menu_id' => $menu->id,
            ],
        ];

        // Footer
        MenuItem::factory()
            ->count(8)
            ->state(new Sequence(
                $footerMenus[0],
                $footerMenus[1],
                $footerMenus[2],
                $footerMenus[3],
                $footerMenus[4],
                $footerMenus[5],
                $footerMenus[6],
                $footerMenus[7],
            ))
            ->create();

        $socialMedias = [
            [
                'title' => 'Facebook',
                'type' => MenuItem::TYPE_URL,
                'order' => 1,
                'url' => 'https://www.facebook.com/',
                'is_blank' => true,
                'icon' => 'fab fa-facebook',
            ],
            [
                'title' => 'Twitter',
                'type' => MenuItem::TYPE_URL,
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
                    ->state(new Sequence(
                        $socialMedias[0],
                        $socialMedias[1]
                    ))
            )
            ->create([
                'locale' => config('app.fallback_locale'),
                'type' => Menu::TYPE_SOCIAL_MEDIA
            ]);

        app(MenuCache::class)->flush();
    }
}
