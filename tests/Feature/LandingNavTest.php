<?php

namespace Tests\Feature;

use App\Models\GlobalOption;
use Database\Seeders\GlobalOptionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\MenuService;
use Modules\Space\Entities\Space;
use Modules\Space\Services\LandingNavService;
use ReflectionMethod;
use Tests\TestCase;

class LandingNavTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GlobalOptionSeeder::class);
    }

    /** @test */
    public function landing_nav_builds_country_menus_with_city_children(): void
    {
        $countryTypeId = GlobalOption::where('name', 'Country')->value('id');
        $cityTypeId = GlobalOption::where('name', 'City')->value('id');

        $country = Space::create([
            'name' => 'Netherlands',
            'type_id' => $countryTypeId,
            'country_code' => 'NLD',
        ]);

        Space::create([
            'name' => 'Amsterdam',
            'type_id' => $cityTypeId,
            'parent_id' => $country->id,
            'city_id' => null,
            'country_code' => 'NLD',
        ]);

        $menus = app(LandingNavService::class)->getCountryCityHeaderMenus(defaultLocale());

        $this->assertCount(1, $menus);
        $this->assertSame('Netherlands', $menus[0]['title']);
        $this->assertCount(1, $menus[0]['children']);
        $this->assertSame('Amsterdam', $menus[0]['children'][0]['title']);
        $this->assertNotEmpty($menus[0]['children'][0]['link']);
    }

    /** @test */
    public function landing_nav_skips_cities_without_published_pages(): void
    {
        $countryTypeId = GlobalOption::where('name', 'Country')->value('id');
        $cityTypeId = GlobalOption::where('name', 'City')->value('id');

        $country = Space::create([
            'name' => 'Sweden',
            'type_id' => $countryTypeId,
            'country_code' => 'SWE',
        ]);

        $city = Space::create([
            'name' => 'Stockholm',
            'type_id' => $cityTypeId,
            'parent_id' => $country->id,
            'country_code' => 'SWE',
        ]);

        $city->is_page_enabled = false;
        $city->save();

        $menus = app(LandingNavService::class)->getCountryCityHeaderMenus(defaultLocale());

        $this->assertCount(1, $menus);
        $this->assertSame('Sweden', $menus[0]['title']);
        $this->assertSame([], $menus[0]['children']);
    }

    /** @test */
    public function landing_nav_merge_strips_legacy_cms_country_items(): void
    {
        $countryTypeId = GlobalOption::where('name', 'Country')->value('id');

        Space::create([
            'name' => 'Netherlands',
            'type_id' => $countryTypeId,
            'country_code' => 'NLD',
        ]);

        $landingMenus = app(LandingNavService::class)->getCountryCityHeaderMenus(defaultLocale());

        $cmsMenus = [
            [
                'title' => 'All Countries',
                'link' => '/en/dead-page',
                'target' => null,
                'isInternalLink' => true,
                'children' => [],
            ],
            [
                'title' => 'City & Pitches',
                'link' => '/en/dead-city-pitches',
                'target' => null,
                'isInternalLink' => true,
                'children' => [],
            ],
            [
                'title' => 'Home',
                'link' => '/en',
                'target' => null,
                'isInternalLink' => true,
                'children' => [],
            ],
        ];

        $method = new ReflectionMethod(MenuService::class, 'mergeLandingNavMenus');
        $method->setAccessible(true);

        $merged = $method->invoke(
            app(MenuService::class),
            $cmsMenus,
            $landingMenus,
            defaultLocale(),
        );

        $titles = collect($merged)->pluck('title')->all();

        $this->assertContains('Netherlands', $titles);
        $this->assertContains('Home', $titles);
        $this->assertNotContains('All Countries', $titles);
        $this->assertNotContains('City & Pitches', $titles);
    }
}
