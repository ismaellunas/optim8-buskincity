<?php

namespace App\Observers;

use App\Entities\Caches\MenuCache;
use App\Models\PageTranslation;
use App\Services\MenuService;

class PageTranslationObserver
{
    protected function flushMenuCache(PageTranslation $pageTranslation)
    {
        if ($pageTranslation->isClearingMenuCacheRequired()) {
            app(MenuCache::class)->flush();
        }
    }

    protected function removeFromMenus(PageTranslation $pageTranslation)
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

    public function saved(PageTranslation $pageTranslation)
    {
        $this->flushMenuCache($pageTranslation);
    }

    public function updated(PageTranslation $pageTranslation)
    {
        $this->removeFromMenus($pageTranslation);
    }

    public function saving(PageTranslation $pageTranslation)
    {
        if ($pageTranslation->isDirty('data')) {
            $pageTranslation->setGeneratedStyle();
        }
    }

    public function deleted(PageTranslation $pageTranslation)
    {
        app(MenuCache::class)->flush();

        $pageTranslation->detachMedia();
    }
}
