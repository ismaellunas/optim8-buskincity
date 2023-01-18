<?php

namespace App\Observers;

use App\Entities\Caches\MenuCache;

class PageTranslationObserver
{
    public function saved($pageTranslation)
    {
        if ($pageTranslation->isClearingMenuCacheRequired()) {
            app(MenuCache::class)->flush();
        }
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
