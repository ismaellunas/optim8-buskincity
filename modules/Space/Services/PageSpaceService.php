<?php

namespace Modules\Space\Services;

use App\Helpers\HumanReadable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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

        return Space::whereDescendantOf($this->space)
            ->whereIsLeaf()
            ->withStructuredUrl($locales)
            ->with('translations', function ($query) use ($locales) {
                $query->inLanguages($locales);
            })
            ->get();
    }

    public function eventDateTimeFormat(string $dateTime): string
    {
        $format = config('constants.format.date_time_event');
        $dateTime = Carbon::parse($dateTime);

        return HumanReadable::dateTimeByUserTimezone($dateTime, $format);
    }
}
