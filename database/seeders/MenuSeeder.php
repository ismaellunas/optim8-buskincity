<?php

namespace Database\Seeders;

use App\Entities\Caches\MenuCache;
use App\Models\{
    PageTranslation,
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
        $pageIds = PageTranslation::select([
                'slug',
                'page_id'
            ])
            ->get()
            ->pluck('page_id', 'slug')
            ->toArray();

        $headerMenus = [
            [
                'title' => 'Home',
                'type' => MenuItem::TYPE_URL,
                'url' => route('homepage'),
                'order' => 1,
            ],
            [
                'title' => 'Street Performers',
                'type' => MenuItem::TYPE_PAGE,
                'order' => 2,
                'page_id' => $pageIds['street-performers'],
            ],
            [
                'title' => 'Blog',
                'type' => MenuItem::TYPE_URL,
                'url' => route('blog.index'),
                'order' => 3,
            ],
            [
                'title' => 'About',
                'type' => MenuItem::TYPE_PAGE,
                'order' => 4,
                'page_id' => $pageIds['about'],
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
                'type' => MenuItem::TYPE_URL,
                'url' => route('blog.index'),
                'order' => 1,
                'parent_id' => $menu->menuItems[0]->id,
                'menu_id' => $menu->id,
            ],
            [
                'title' => 'About',
                'type' => MenuItem::TYPE_PAGE,
                'order' => 2,
                'parent_id' => $menu->menuItems[0]->id,
                'page_id' => $pageIds['about'],
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
                'title' => 'Street Performers',
                'type' => MenuItem::TYPE_PAGE,
                'order' => 1,
                'parent_id' => $menu->menuItems[1]->id,
                'page_id' => $pageIds['street-performers'],
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
            ->state(new Sequence(...$footerMenus))
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
                    ->state(new Sequence(...$socialMedias))
            )
            ->create([
                'locale' => config('app.fallback_locale'),
                'type' => Menu::TYPE_SOCIAL_MEDIA
            ]);

        app(MenuCache::class)->flush();
    }
}
