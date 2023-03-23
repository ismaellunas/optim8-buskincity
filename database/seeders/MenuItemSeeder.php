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

class MenuItemSeeder extends Seeder
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

        $streetPerformerPageId = $pageIds['street-performers'] ?? null;
        $aboutPageId = $pageIds['about'] ?? null;

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
                'menu_itemable_id' => $streetPerformerPageId,
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
                'menu_itemable_id' => $aboutPageId,
                'menu_itemable_type' => $pageOption->getTypeOptions()['model'],
            ],
        ];

        $headerMenu = Menu::header()->first();

        MenuItem::factory()
            ->count(4)
            ->state(new Sequence(...$headerMenus))
            ->for($headerMenu)
            ->create();

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

        $footerMenu = Menu::footer()->first();

        MenuItem::factory()
            ->count(3)
            ->state(new Sequence(...$footerMenus))
            ->for($footerMenu)
            ->create();

        $footerMenus = [
            [
                'title' => 'Blog',
                'type' => $urlOption->getKey(),
                'url' => route('blog.index'),
                'order' => 1,
                'parent_id' => $footerMenu->menuItems[0]->id,
                'menu_id' => $footerMenu->id,
            ],
            [
                'title' => 'About',
                'type' => $pageOption->getKey(),
                'url' => '#',
                'order' => 2,
                'parent_id' => $footerMenu->menuItems[0]->id,
                'menu_id' => $footerMenu->id,
                'menu_itemable_id' => $aboutPageId,
                'menu_itemable_type' => $pageOption->getTypeOptions()['model'],
            ],
            [
                'title' => 'Contact us',
                'type' => $urlOption->getKey(),
                'url' => '#',
                'order' => 3,
                'parent_id' => $footerMenu->menuItems[0]->id,
                'menu_id' => $footerMenu->id,
            ],
            [
                'title' => 'Street Performer',
                'type' => $pageOption->getKey(),
                'url' => '#',
                'order' => 1,
                'parent_id' => $footerMenu->menuItems[1]->id,
                'menu_id' => $footerMenu->id,
                'menu_itemable_id' => $streetPerformerPageId,
                'menu_itemable_type' => $pageOption->getTypeOptions()['model'],
            ],
            [
                'title' => 'Become a Performer',
                'type' => $urlOption->getKey(),
                'url' => '#',
                'order' => 2,
                'parent_id' => $footerMenu->menuItems[1]->id,
                'menu_id' => $footerMenu->id,
            ],
            [
                'title' => 'Terms & Conditions',
                'type' => $urlOption->getKey(),
                'url' => '#',
                'order' => 1,
                'parent_id' => $footerMenu->menuItems[2]->id,
                'menu_id' => $footerMenu->id,
            ],
            [
                'title' => 'Privacy Policy',
                'type' => $urlOption->getKey(),
                'url' => '#',
                'order' => 2,
                'parent_id' => $footerMenu->menuItems[2]->id,
                'menu_id' => $footerMenu->id,
            ],
            [
                'title' => 'Cookie Policy',
                'type' => $urlOption->getKey(),
                'url' => '#',
                'order' => 3,
                'parent_id' => $footerMenu->menuItems[2]->id,
                'menu_id' => $footerMenu->id,
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

        $socialMediaMenu = Menu::socialMedia()->first();

        MenuItem::factory()
            ->count(2)
            ->state(new Sequence(...$socialMedias))
            ->for($socialMediaMenu)
            ->create();

        app(MenuCache::class)->flush();
    }
}
