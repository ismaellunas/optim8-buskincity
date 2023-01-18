<?php

namespace Modules\Space\Observers;

use App\Entities\Caches\MenuCache;
use App\Observers\PageTranslationObserver as AppPageTranslationObserver;
use App\Services\MenuService;
use Modules\Space\Entities\PageTranslation;

class PageTranslationObserver extends AppPageTranslationObserver
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
                $pageTranslation->page->space,
                $pageTranslation->locale
            );
        }
    }
}
