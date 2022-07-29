<?php

namespace Modules\Space\Services;

use Modules\Space\Entities\Space;
use App\Helpers\HumanReadable;

class PageSpaceService
{
    private Space $space;

    public function __construct()
    {
        $pageTranslation = null;

        if (request()->route()) {
            $pageTranslation = request()->route()->parameter('page_translation');
        }

        $space = $pageTranslation->page->space ?? null;

        if ($space) {
            $this->space = $space;
        }
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
        $lastChildren = collect();

        $firstChildern = $this->space->children()->get();

        foreach ($firstChildern as $firstChild) {
            $secondChildren = $firstChild->children();

            if ($secondChildren->exists()) {
                foreach ($secondChildren->get() as $secondChild) {

                    $lastChildren->push($secondChild);

                }
            } else {

                $lastChildren->push($firstChild);

            }
        }

        return $lastChildren->sortBy('name')->all();
    }

    public function defaultLogoUrl(): string
    {
        return 'https://dummyimage.com/600x600/ccc/fff.png&text=+';
    }
}