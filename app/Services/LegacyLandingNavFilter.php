<?php

namespace App\Services;

use App\Contracts\MenuInterface;
use App\Models\Country;
use App\Models\MenuItem;
use Illuminate\Support\Collection;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Modules\Space\Entities\Space;

/**
 * Filters legacy manual country/city header navigation superseded by LandingNavService (FR-NAV-1).
 *
 * Sources traced in MenuService::getThemeHeaderMenu():
 * - CMS `menu_items` rows rendered via createStructuredMenus() → frontendMenuArrayFormater()
 * - Auto-generated country→city items from LandingNavService (Space tree)
 */
class LegacyLandingNavFilter
{
    /**
     * @param  Collection<int, MenuInterface>  $menus
     * @return Collection<int, MenuInterface>
     */
    public static function filterStructuredHeaderMenus(Collection $menus, string $locale): Collection
    {
        return $menus
            ->reject(fn (MenuInterface $menu) => self::isLegacyCmsHeaderMenu($menu, $locale))
            ->map(function (MenuInterface $menu) use ($locale) {
                if (property_exists($menu, 'children') && $menu->children instanceof Collection && $menu->children->isNotEmpty()) {
                    $menu->children = self::filterStructuredHeaderMenus($menu->children, $locale);
                }

                return $menu;
            })
            ->values();
    }

    /**
     * Legacy CMS header items were hand-built in Theme → Header using these shapes:
     * - type=url            → /spaces index ("All Countries", "Country")
     * - type=space          → linked Country Space ("Netherlands", …)
     * - type=segment (+url) → parent grouping only space/url children ("City & Pitches")
     */
    public static function isLegacyCmsHeaderMenu(MenuInterface $menu, string $locale): bool
    {
        $item = $menu->getModel();

        if ($item->type === 'url' && self::urlPointsToSpacesIndex($item->url, $locale)) {
            return true;
        }

        if ($item->type === 'space' && self::menuItemLinksToCountrySpace($item)) {
            return true;
        }

        if (in_array($item->type, ['segment', 'url'], true)) {
            return self::isLegacyGeoDrillDownGroup($menu, $locale);
        }

        return false;
    }

    /**
     * Landing nav countries must map to a real row in `countries` via `spaces.country_code`.
     */
    public static function isNavigableCountrySpace(Space $country): bool
    {
        if (blank($country->country_code)) {
            return false;
        }

        $alpha2 = app(CountryService::class)->toAlpha2($country->country_code);

        return $alpha2 !== null
            && Country::where('alpha2', $alpha2)->exists();
    }

    public static function urlPointsToSpacesIndex(?string $url, string $locale): bool
    {
        if (blank($url)) {
            return false;
        }

        $spacesIndexUrl = route('frontend.spaces.index');
        $localizedIndexUrl = LaravelLocalization::localizeURL($spacesIndexUrl, $locale);

        if (in_array($url, [$spacesIndexUrl, $localizedIndexUrl], true)) {
            return true;
        }

        $path = parse_url($url, PHP_URL_PATH);

        return is_string($path)
            && str_ends_with(rtrim($path, '/'), '/spaces');
    }

    private static function menuItemLinksToCountrySpace(MenuItem $item): bool
    {
        if ($item->menu_itemable_type !== Space::class) {
            return false;
        }

        $space = $item->menuItemable;

        if (! $space instanceof Space) {
            return false;
        }

        $space->loadMissing('type');

        return ($space->type->name ?? null) === 'Country';
    }

    /**
     * "City & Pitches" lived as a segment/url parent whose children were Space or /spaces links.
     */
    private static function isLegacyGeoDrillDownGroup(MenuInterface $menu, string $locale): bool
    {
        if (! property_exists($menu, 'children') || ! ($menu->children instanceof Collection)) {
            return false;
        }

        if ($menu->children->isEmpty()) {
            return false;
        }

        return $menu->children->every(function (MenuInterface $child) use ($locale) {
            $childItem = $child->getModel();

            if ($childItem->type === 'space') {
                return true;
            }

            if ($childItem->type === 'url') {
                return self::urlPointsToSpacesIndex($childItem->url, $locale)
                    || blank($childItem->url)
                    || $childItem->url === '#';
            }

            return false;
        });
    }
}
