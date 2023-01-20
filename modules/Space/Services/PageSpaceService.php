<?php

namespace Modules\Space\Services;

use App\Helpers\HumanReadable;
use App\Services\TranslationService;
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
        $locales = collect([
            app(TranslationService::class)->getDefaultLocale(),
            app(TranslationService::class)->currentLanguage(),
        ])->unique()->all();

        return Space::whereDescendantOf($this->space)
            ->whereIsLeaf()
            ->with([
                'page.translations' => function ($query) use ($locales) {
                    $query->select('id', 'page_id', 'locale', 'status');
                    $query->where('locale', $locales);
                    $query->with('page.space.ancestors', function ($query) use ($locales) {
                        $query->select(['id', 'page_id','_lft','_rgt', 'parent_id', 'is_page_enabled', 'type_id']);
                        $query->with('page.translations', function ($query) use ($locales) {
                            $query->select('id', 'page_id', 'locale', 'status', 'slug','unique_key');
                            $query->where('locale', $locales);
                        });
                    });
                },
            ])
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