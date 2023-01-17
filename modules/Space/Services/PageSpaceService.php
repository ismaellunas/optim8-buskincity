<?php

namespace Modules\Space\Services;

use App\Helpers\HumanReadable;
use Illuminate\Support\Carbon;
use Modules\Space\Entities\PageTranslation;
use Modules\Space\Entities\Space;
use Modules\Space\ModuleService;

class PageSpaceService
{
    private Space $space;

    public function __construct()
    {
        $pageTranslation = $this->getPageTranslationFromRequest(request());

        $this->space = $pageTranslation->page->space ?? null;
    }

    private function getPageTranslationFromRequest($request): ?PageTranslation
    {
        $slugs = collect(explode('/', $request->slugs));

        if ($slugs->isNotEmpty()) {
            return PageTranslation::whereSlug($slugs->last())->first();
        }

        return null;
    }

    public function getPhoneNumberFormat(array $phone): string
    {
        if (!isset($phone['number']) || !isset($phone['country'])) {
            return '-';
        }

        return HumanReadable::phoneNumberFormat($phone['number'], $phone['country']);
    }

    public function getChildren(): array
    {
        $allChildren = [];

        foreach ($this->space->descendants->all() as $child) {
            if (!$child->children()->exists()) {
                $allChildren[] = $child;
            }
        }

        return $allChildren;
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