<?php

namespace Database\Seeders;

use App\Entities\Caches\MenuCache;
use App\Models\Menu;
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
        // Header
        Menu::factory()
            ->state(new Sequence(
                ['type' => Menu::TYPE_HEADER],
            ))
            ->create([
                'locale' => config('app.fallback_locale'),
            ]);

        // Footer
        Menu::factory()
            ->state(new Sequence(
                ['type' => Menu::TYPE_FOOTER],
            ))
            ->create([
                'locale' => config('app.fallback_locale'),
            ]);

        // Social Media
        Menu::factory()
            ->create([
                'locale' => config('app.fallback_locale'),
                'type' => Menu::TYPE_SOCIAL_MEDIA
            ]);

        app(MenuCache::class)->flush();
    }
}
