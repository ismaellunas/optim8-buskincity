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
     * - type=url, url=#     → placeholder parent ("City & Pitches", menu_items.id 67 on staging)
     * - type=url            → /spaces index ("All Countries", "Country")
     * - type=space          → linked geo Space (Country/City/Pitch)
     * - type=segment (+url) → parent grouping with geo children
     */
    public static function isLegacyCmsHeaderMenu(MenuInterface $menu, string $locale): bool
    {
        $item = $menu->getModel();

        if ($item->type === 'url' && self::isPlaceholderUrl($item->url)) {
            return true;
        }

        if ($item->type === 'url' && self::urlPointsToSpacesIndex($item->url, $locale)) {
            return true;
        }

        if ($item->type === 'space' && self::menuItemLinksToGeoSpace($item)) {
            return true;
        }

        if (self::menuUrlIsSpaceLanding($menu, $locale)) {
            return true;
        }

        if (in_array($item->type, ['segment', 'url'], true)) {
            return self::isLegacyGeoDrillDownGroup($menu, $locale);
        }

        return false;
    }

    /**
     * Country spaces need a resolvable ISO code, or a name matching `countries.display_name`
     * (legacy rows often lack `country_code`, e.g. Norway on staging).
     */
    public static function isNavigableCountrySpace(Space $country): bool
    {
        if (filled($country->country_code)) {
            $alpha2 = app(CountryService::class)->toAlpha2($country->country_code);

            if ($alpha2 !== null && Country::where('alpha2', $alpha2)->exists()) {
                return true;
            }
        }

        return Country::query()
            ->whereRaw('LOWER(display_name) = ?', [strtolower(trim($country->name))])
            ->exists();
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

    private static function isPlaceholderUrl(?string $url): bool
    {
        return in_array(trim($url ?? ''), ['', '#'], true);
    }

    private static function menuItemLinksToGeoSpace(MenuItem $item): bool
    {
        if ($item->menu_itemable_type !== Space::class) {
            return false;
        }

        $space = $item->menuItemable;

        if (! $space instanceof Space) {
            return false;
        }

        $space->loadMissing('type');

        return in_array($space->type->name ?? null, ['Country', 'City', 'Pitch'], true);
    }

    private static function menuUrlIsSpaceLanding(MenuInterface $menu, string $locale): bool
    {
        try {
            $url = $menu->getUrl();
        } catch (\Throwable) {
            return false;
        }

        if (blank($url) || $url === '#') {
            return false;
        }

        if (self::urlPointsToSpacesIndex($url, $locale)) {
            return true;
        }

        $path = parse_url($url, PHP_URL_PATH);

        return is_string($path) && preg_match('#/spaces/.+#', $path) === 1;
    }

    /**
     * Legacy drill-down groups ("City & Pitches") mixed page/space/url children.
     */
    private static function isLegacyGeoDrillDownGroup(MenuInterface $menu, string $locale): bool
    {
        if (! property_exists($menu, 'children') || ! ($menu->children instanceof Collection)) {
            return false;
        }

        if ($menu->children->isEmpty()) {
            return false;
        }

        if (self::menuUrlIsSpaceLanding($menu, $locale)) {
            return true;
        }

        return $menu->children->contains(function (MenuInterface $child) use ($locale) {
            $childItem = $child->getModel();

            if ($childItem->type === 'space') {
                return true;
            }

            if ($childItem->type === 'url') {
                return self::isPlaceholderUrl($childItem->url)
                    || self::urlPointsToSpacesIndex($childItem->url, $locale);
            }

            return self::menuUrlIsSpaceLanding($child, $locale);
        });
    }
}
