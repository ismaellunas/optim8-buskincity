<?php

namespace Database\Seeders;

use App\Entities\Caches\MenuCache;
use App\Entities\Menus\Options\SegmentOption;
use App\Entities\Menus\Options\UrlOption;
use App\Entities\Menus\Options\PageOption;
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
        $urlOption = new UrlOption();
        $pageOption = new PageOption();
        $segmentOption = new SegmentOption();

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
                'type' => $urlOption->getKey(),
                'url' => route('homepage'),
                'order' => 1,
            ],
            [
                'title' => 'Street Performers',
                'type' => $pageOption->getKey(),
                'order' => 2,
                'menu_itemable_id' => $pageIds['street-performers'],
                'menu_itemable_type' => $pageOption->getTypeOptions()['model'],
            ],
            [
                'title' => 'Blog',
                'type' => $urlOption->getKey(),
                'url' => route('blog.index'),
                'order' => 3,
            ],
            [
                'title' => 'About',
                'type' => $pageOption->getKey(),
                'order' => 4,
                'menu_itemable_id' => $pageIds['about'],
                'menu_itemable_type' => $pageOption->getTypeOptions()['model'],
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
                'type' => $segmentOption->getKey(),
                'order' => 1,
            ],
            [
                'title' => 'Performers',
                'type' => $segmentOption->getKey(),
                'order' => 2,
            ],
            [
                'title' => 'General',
                'type' => $segmentOption->getKey(),
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
                'type' => $urlOption->getKey(),
                'url' => route('blog.index'),
                'order' => 1,
                'parent_id' => $menu->menuItems[0]->id,
                'menu_id' => $menu->id,
            ],
            [
                'title' => 'About',
                'type' => $pageOption->getKey(),
                'url' => '#',
                'order' => 2,
                'parent_id' => $menu->menuItems[0]->id,
                'menu_id' => $menu->id,
                'menu_itemable_id' => $pageIds['about'],
                'menu_itemable_type' => $pageOption->getTypeOptions()['model'],
            ],
            [
                'title' => 'Contact us',
                'type' => $urlOption->getKey(),
                'url' => '#',
                'order' => 3,
                'parent_id' => $menu->menuItems[0]->id,
                'menu_id' => $menu->id,
            ],
            [
                'title' => 'Street Performer',
                'type' => $pageOption->getKey(),
                'url' => '#',
                'order' => 1,
                'parent_id' => $menu->menuItems[1]->id,
                'menu_id' => $menu->id,
                'menu_itemable_id' => $pageIds['street-performers'],
                'menu_itemable_type' => $pageOption->getTypeOptions()['model'],
            ],
            [
                'title' => 'Become a Performer',
                'type' => $urlOption->getKey(),
                'url' => '#',
                'order' => 2,
                'parent_id' => $menu->menuItems[1]->id,
                'menu_id' => $menu->id,
            ],
            [
                'title' => 'Terms & Conditions',
                'type' => $urlOption->getKey(),
                'url' => '#',
                'order' => 1,
                'parent_id' => $menu->menuItems[2]->id,
                'menu_id' => $menu->id,
            ],
            [
                'title' => 'Privacy Policy',
                'type' => $urlOption->getKey(),
                'url' => '#',
                'order' => 2,
                'parent_id' => $menu->menuItems[2]->id,
                'menu_id' => $menu->id,
            ],
            [
                'title' => 'Cookie Policy',
                'type' => $urlOption->getKey(),
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
                'type' => $urlOption->getKey(),
                'order' => 1,
                'url' => 'https://www.facebook.com/',
                'is_blank' => true,
                'icon' => 'fab fa-facebook',
            ],
            [
                'title' => 'Twitter',
                'type' => $urlOption->getKey(),
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
