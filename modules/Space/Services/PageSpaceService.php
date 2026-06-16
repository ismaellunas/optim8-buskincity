<?php

namespace Modules\Space\Services;

use App\Helpers\HumanReadable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Enums\ProductStatus;
use Modules\Space\Entities\PageTranslation;
use Modules\Space\Entities\Space;
use Modules\Space\ModuleService;

class PageSpaceService
{
    private Space $space;
    private $pageTranslation;

    public function __construct()
    {
        $pageTranslation = $this->getPageTranslationFromRequest();

        if ($pageTranslation && $pageTranslation->page->space) {
            $this->space = $pageTranslation->page->space;
        }
    }

    public function getPageTranslationFromRequest(): ?PageTranslation
    {
        if (is_null($this->pageTranslation)) {

            $slugs = collect(explode('/', request()->slugs))
                ->filter(fn ($segment) => $segment !== '' && $segment !== null)
                ->values();

            if ($slugs->isEmpty()) {
                $this->pageTranslation = null;

                return $this->pageTranslation;
            }

            $path = $slugs->implode('/');

            $candidates = PageTranslation::whereSlug($slugs->last())->get();

            // Disambiguate by the full ancestor path so deep URLs with colliding
            // leaf slugs resolve to the correct page (not just the last segment).
            // Falls back to prior behavior (first match) when no full path matches.
            $this->pageTranslation = $candidates
                ->first(fn (PageTranslation $translation) => $translation->getSlugs() === $path)
                ?? $candidates->first();
        }

        return $this->pageTranslation;
    }

    public function getPhoneNumberFormat(array $phone): string
    {
        if (!isset($phone['number']) || !isset($phone['country'])) {
            return '-';
        }

        return HumanReadable::phoneNumberFormat($phone['number'], $phone['country']);
    }

    public function getLeaves(): Collection
    {
        $locales = collect([currentLocale(), defaultLocale()])->unique()->all();

        $pitchTypeIds = app(SpaceService::class)->types()
            ->whereIn('name', ['Pitch', 'Special Events / Festivals'])
            ->pluck('id');

        return Space::whereDescendantOf($this->space)
            ->whereIsLeaf()
            ->when(
                $pitchTypeIds->isNotEmpty(),
                fn ($query) => $query->whereIn('type_id', $pitchTypeIds)
            )
            ->isPageEnabled(true)
            ->withStructuredUrl($locales)
            ->with('translations', function ($query) use ($locales) {
                $query->inLanguages($locales);
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Pitch cards to render on a City public page.
     *
     * Includes tree-descendant pitches AND Spaces linked only from Products
     * (via productable_type=Space, productable_id) that belong to this city,
     * so cities like Borås surface pitches that exist only as Product records
     * (no Space tree descendant).
     */
    public function getCityPitches(): Collection
    {
        if (! isset($this->space)) {
            return collect();
        }

        $locales = collect([currentLocale(), defaultLocale()])->unique()->all();

        // 1. Pitch Spaces from tree descendants + same city_id (existing logic).
        $treeSpaces = app(SpaceService::class)
            ->pitchSpacesForContextQuery($this->space)
            ->isPageEnabled(true)
            ->withStructuredUrl($locales)
            ->with('translations', fn ($q) => $q->inLanguages($locales))
            ->orderBy('name')
            ->get();

        // 2. Spaces linked from Products by city_id that aren't already included.
        $treeSpaceIds = $treeSpaces->pluck('id');
        $productSpaces = $this->pitchSpacesFromProducts($this->space, $treeSpaceIds, $locales);

        return $treeSpaces->concat($productSpaces)->unique('id')->sortBy('name')->values();
    }

    /**
     * Find Spaces linked from pitch Products (via productable) that belong to
     * this city but aren't already in the Space tree descendants.
     */
    private function pitchSpacesFromProducts(Space $space, Collection $excludeIds, array $locales): Collection
    {
        if (! $space->city_id) {
            return collect();
        }

        $productSpaceIds = Product::query()
            ->where('status', ProductStatus::PUBLISHED->value)
            ->whereHas('eventSchedule')
            ->where('city_id', $space->city_id)
            ->where('productable_type', Space::class)
            ->whereNotNull('productable_id')
            ->when($excludeIds->isNotEmpty(), fn ($q) => $q->whereNotIn('productable_id', $excludeIds))
            ->pluck('productable_id')
            ->unique();

        if ($productSpaceIds->isEmpty()) {
            return collect();
        }

        return Space::whereIn('id', $productSpaceIds)
            ->isPageEnabled(true)
            ->withStructuredUrl($locales)
            ->with('translations', fn ($q) => $q->inLanguages($locales))
            ->orderBy('name')
            ->get();
    }

    public function eventDateTimeFormat(string $dateTime): string
    {
        $format = config('constants.format.date_time_event');
        $dateTime = Carbon::parse($dateTime);

        return HumanReadable::dateTimeByUserTimezone($dateTime, $format);
    }
}
