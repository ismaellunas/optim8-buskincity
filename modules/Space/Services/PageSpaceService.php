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

        $this->space = $pageTranslation->page->space ?? null;
    }

    public function getPageTranslationFromRequest(): ?PageTranslation
    {
        if (is_null($this->pageTranslation)) {

            $slugs = collect(explode('/', request()->slugs));

            if ($slugs->isNotEmpty()) {
                $this->pageTranslation = PageTranslation::whereSlug($slugs->last())->first();
            } else {
                $this->pageTranslation = null;
            }
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

    public function defaultLogoUrl(): string
    {
        return ModuleService::defaultLogoUrl();
    }

    public function eventDateTimeFormat(string $dateTime): string
    {
        $format = config('constants.format.date_time_event');
        $dateTime = Carbon::parse($dateTime);

        return HumanReadable::dateTimeByUserTimezone($dateTime, $format);
    }
}