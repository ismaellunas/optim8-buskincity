<?php

namespace Modules\Space\Services;

use App\Services\LegacyLandingNavFilter;
use Modules\Space\Entities\Space;

/**
 * Builds the public landing drill-down nav from the Space tree.
 *
 * Header shape (3 levels): "City & Pitches" → Country → City.
 * Page-level drill-down (2 more levels): city page lists pitches via
 * PageSpaceService::getLeaves(); pitch page lists events via the
 * <space-events> component.
 */
class LandingNavService
{
    /**
     * Top-level "City & Pitches" wrapper containing country → city children.
     * Returns [] when there are no navigable countries (no orphan parent).
     *
     * @return array<int, array{title: string, link: string, target: null, isInternalLink: bool, children: array}>
     */
    public function getLandingHeaderMenus(string $locale): array
    {
        $countryMenus = $this->getCountryCityHeaderMenus($locale);

        if ($countryMenus === []) {
            return [];
        }

        return [
            $this->menuItem(__('City & Pitches'), '#', $countryMenus),
        ];
    }

    /**
     * @return array<int, array{title: string, link: string, target: null, isInternalLink: bool, children: array}>
     */
    public function getCountryCityHeaderMenus(string $locale): array
    {
        $types = app(SpaceService::class)->types();
        $countryTypeId = $types->firstWhere('name', 'Country')?->id;
        $cityTypeId = $types->firstWhere('name', 'City')?->id;

        if (! $countryTypeId || ! $cityTypeId) {
            return [];
        }

        $locales = array_values(array_unique([$locale, defaultLocale()]));

        $countries = Space::query()
            ->where('type_id', $countryTypeId)
            ->isPageEnabled(true)
            ->with([
                'children' => function ($query) use ($cityTypeId, $locales) {
                    $query->where('type_id', $cityTypeId)
                        ->isPageEnabled(true)
                        ->orderBy('name')
                        ->withStructuredUrl($locales);
                },
            ])
            ->withStructuredUrl($locales)
            ->orderBy('name')
            ->get();

        $menus = [];

        foreach ($countries as $country) {
            if (! LegacyLandingNavFilter::isNavigableCountrySpace($country)) {
                continue;
            }

            if (! $country->hasEnabledPage()) {
                continue;
            }

            $countryLink = $this->safePageUrl($country, $locale);

            if ($countryLink === null) {
                continue;
            }

            $cityChildren = [];

            foreach ($country->children as $city) {
                if (! $city->hasEnabledPage()) {
                    continue;
                }

                $cityLink = $this->safePageUrl($city, $locale);

                if ($cityLink === null) {
                    continue;
                }

                $cityChildren[] = $this->menuItem($city->name, $cityLink);
            }

            $menus[] = $this->menuItem($country->name, $countryLink, $cityChildren);
        }

        return $menus;
    }

    /**
     * @param  array<int, array<string, mixed>>  $children
     * @return array{title: string, link: string, target: null, isInternalLink: bool, children: array}
     */
    private function menuItem(string $title, string $link, array $children = []): array
    {
        return [
            'title' => $title,
            'link' => $link,
            'target' => null,
            'isInternalLink' => true,
            'children' => $children,
        ];
    }

    private function safePageUrl(Space $space, string $locale): ?string
    {
        try {
            return $space->pageLocalizeURL($locale);
        } catch (\Throwable) {
            return null;
        }
    }
}
