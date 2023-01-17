<?php

namespace App\Observers;

use App\Entities\Caches\MenuCache;
use App\Models\PageTranslation;
use App\Services\MenuService;

class PageTranslationObserver
{
    protected function flushMenuCache($pageTranslation)
    {
        if ($pageTranslation->isClearingMenuCacheRequired()) {
            app(MenuCache::class)->flush();
        }
    }

    protected function removeFromMenus($pageTranslation)
    {
        if (
            $pageTranslation->isDirty('status')
            && $pageTranslation->getOriginal('status') == PageTranslation::STATUS_PUBLISHED
        ) {
            app(MenuService::class)->removeModelFromMenus(
                $pageTranslation->page,
                $pageTranslation->locale
            );
        }
    }

    public function saved($pageTranslation)
    {
        $this->flushMenuCache($pageTranslation);
    }

    public function updated($pageTranslation)
    {
        $this->removeFromMenus($pageTranslation);
    }

    public function saving($pageTranslation)
    {
        if ($pageTranslation->isDirty('data')) {
            $pageTranslation->setGeneratedStyle();
        }
    }

    public function deleted($pageTranslation)
    {
        app(MenuCache::class)->flush();
    }
}
