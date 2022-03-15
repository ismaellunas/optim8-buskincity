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
        $pageIds = Page::pluck('id');
        $post = Post::published()->first();

        $menus = [
            [
                'title' => 'Dummy Page',
                'type' => MenuItem::TYPE_PAGE,
                'order' => 1,
                'page_id' => $pageIds->first(),
            ],
            [
                'title' => 'Good News',
                'type' => MenuItem::TYPE_POST,
                'order' => 2,
                'post_id' => $post->id,
            ],
            [
                'title' => 'FAQ',
                'type' => MenuItem::TYPE_PAGE,
                'order' => 3,
                'page_id' => $pageIds->last(),
            ],
        ];

        // Header and Footer
        Menu::factory()
            ->count(2)
            ->has(
                MenuItem::factory()
                    ->count(3)
                    ->state(new Sequence(
                        $menus[0],
                        $menus[1],
                        $menus[2],
                    ))
            )
            ->state(new Sequence(
                ['type' => Menu::TYPE_HEADER],
                ['type' => Menu::TYPE_FOOTER],
            ))
            ->create([
                'locale' => config('app.fallback_locale'),
            ]);

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
