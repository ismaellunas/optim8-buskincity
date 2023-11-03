<?php

namespace Database\Seeders;

use App\Entities\Caches\MenuCache;
use App\Entities\Menus\Options\SegmentOption;
use App\Entities\Menus\Options\UrlOption;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuBasicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->populateMenus();
        $this->populateMenuItems();

        $this->clearCache();
    }

    private function populateMenus(): void
    {
        Menu::factory()
            ->create([
                'locale' => config('app.fallback_locale'),
                'type' => Menu::TYPE_HEADER
            ]);

        Menu::factory()
            ->create([
                'locale' => config('app.fallback_locale'),
                'type' => Menu::TYPE_FOOTER
            ]);
    }

    private function populateMenuItems(): void
    {
        $urlOption = new UrlOption();
        $segmentOption = new SegmentOption();

        // Header
        $headerMenuItem = [
            'title' => __('Blog'),
            'type' => $urlOption->getKey(),
            'url' => route('blog.index'),
            'order' => 1,
        ];

        $headerMenu = Menu::header()->first();

        MenuItem::factory()
            ->for($headerMenu)
            ->create($headerMenuItem);

        // Footer
        $footerSegment = [
            'title' => __('About'),
            'type' => $segmentOption->getKey(),
            'order' => 1,
        ];

        $footerMenu = Menu::footer()->first();

        $footerSegment = MenuItem::factory()
            ->for($footerMenu)
            ->create($footerSegment);

        $footerMenuItem = [
            'title' => __('Blog'),
            'type' => $urlOption->getKey(),
            'url' => route('blog.index'),
            'order' => 1,
            'parent_id' => $footerSegment->id,
        ];

        MenuItem::factory()
            ->for($footerMenu)
            ->create($footerMenuItem);
    }

    private function clearCache(): void
    {
        app(MenuCache::class)->flush();
    }
}
