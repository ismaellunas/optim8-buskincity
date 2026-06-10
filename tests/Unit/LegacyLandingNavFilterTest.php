<?php

namespace Tests\Unit;

use App\Entities\Menus\Options\SegmentOption;
use App\Entities\Menus\Options\UrlOption;
use App\Entities\Menus\SegmentMenu;
use App\Entities\Menus\UrlMenu;
use App\Models\Country;
use App\Models\GlobalOption;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\LegacyLandingNavFilter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Modules\Space\Entities\Space;
use Modules\Space\Menus\MenuUrl;
use Tests\TestCase;

class LegacyLandingNavFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\GlobalOptionSeeder::class);

        Country::create([
            'alpha2' => 'NL',
            'alpha3' => 'NLD',
            'display_name' => 'Netherlands',
        ]);
    }

    /** @test */
    public function it_detects_spaces_index_url_menu_items(): void
    {
        $locale = defaultLocale();
        $url = route('frontend.spaces.index');

        $this->assertTrue(LegacyLandingNavFilter::urlPointsToSpacesIndex($url, $locale));
        $this->assertTrue(
            LegacyLandingNavFilter::urlPointsToSpacesIndex(
                LaravelLocalization::localizeURL($url, $locale),
                $locale
            )
        );
        $this->assertFalse(LegacyLandingNavFilter::urlPointsToSpacesIndex(route('homepage'), $locale));
    }

    /** @test */
    public function it_keeps_only_country_spaces_with_a_real_countries_row(): void
    {
        $countryTypeId = GlobalOption::where('name', 'Country')->value('id');

        $valid = Space::create([
            'name' => 'Netherlands',
            'type_id' => $countryTypeId,
            'country_code' => 'NLD',
        ]);

        $invalid = Space::create([
            'name' => 'City & Pitches',
            'type_id' => $countryTypeId,
            'country_code' => null,
        ]);

        $this->assertTrue(LegacyLandingNavFilter::isNavigableCountrySpace($valid));
        $this->assertFalse(LegacyLandingNavFilter::isNavigableCountrySpace($invalid));
    }

    /** @test */
    public function it_filters_legacy_segment_grouping_space_children(): void
    {
        $headerMenu = Menu::factory()->create([
            'type' => Menu::TYPE_HEADER,
            'locale' => defaultLocale(),
        ]);

        $cityTypeId = GlobalOption::where('name', 'City')->value('id');
        $citySpace = Space::create([
            'name' => 'Amsterdam',
            'type_id' => $cityTypeId,
            'country_code' => 'NLD',
        ]);

        $segmentItem = MenuItem::create([
            'title' => 'City & Pitches',
            'type' => (new SegmentOption())->getKey(),
            'order' => 1,
            'menu_id' => $headerMenu->id,
        ]);

        $childItem = MenuItem::create([
            'title' => 'Amsterdam',
            'type' => 'space',
            'order' => 1,
            'parent_id' => $segmentItem->id,
            'menu_id' => $headerMenu->id,
            'menu_itemable_id' => $citySpace->id,
            'menu_itemable_type' => Space::class,
        ]);

        $segmentMenu = new SegmentMenu($segmentItem, defaultLocale());
        $segmentMenu->children = collect([
            new MenuUrl($childItem, defaultLocale()),
        ]);

        $this->assertTrue(
            LegacyLandingNavFilter::isLegacyCmsHeaderMenu($segmentMenu, defaultLocale())
        );

        $filtered = LegacyLandingNavFilter::filterStructuredHeaderMenus(
            collect([$segmentMenu]),
            defaultLocale()
        );

        $this->assertCount(0, $filtered);
    }

    /** @test */
    public function it_filters_cms_menu_items_linked_to_country_spaces(): void
    {
        $countryTypeId = GlobalOption::where('name', 'Country')->value('id');
        $countrySpace = Space::create([
            'name' => 'Netherlands',
            'type_id' => $countryTypeId,
            'country_code' => 'NLD',
        ]);

        $menuItem = MenuItem::create([
            'title' => 'Netherlands',
            'type' => 'space',
            'order' => 1,
            'menu_id' => Menu::factory()->create([
                'type' => Menu::TYPE_HEADER,
                'locale' => defaultLocale(),
            ])->id,
            'menu_itemable_id' => $countrySpace->id,
            'menu_itemable_type' => Space::class,
        ]);

        $menu = new MenuUrl($menuItem->fresh('menuItemable'), defaultLocale());

        $this->assertTrue(LegacyLandingNavFilter::isLegacyCmsHeaderMenu($menu, defaultLocale()));
    }

    /** @test */
    public function it_keeps_normal_url_menu_items(): void
    {
        $menuItem = MenuItem::create([
            'title' => 'Home',
            'type' => (new UrlOption())->getKey(),
            'url' => route('homepage'),
            'order' => 1,
            'menu_id' => Menu::factory()->create([
                'type' => Menu::TYPE_HEADER,
                'locale' => defaultLocale(),
            ])->id,
        ]);

        $menu = new UrlMenu($menuItem, defaultLocale());

        $this->assertFalse(LegacyLandingNavFilter::isLegacyCmsHeaderMenu($menu, defaultLocale()));
    }
}
