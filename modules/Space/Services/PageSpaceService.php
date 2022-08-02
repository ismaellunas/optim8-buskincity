<?php

namespace Modules\Space\Services;

use App\Helpers\HumanReadable;
use Modules\Space\Entities\Space;
use Modules\Space\ModuleService;

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
}