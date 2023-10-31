<?php

namespace App\Observers;

use App\Entities\Caches\MenuCache;
use App\Models\PageTranslation;

class PageTranslationObserver
{
    protected function flushMenuCache(PageTranslation $pageTranslation)
    {
        if ($pageTranslation->isClearingMenuCacheRequired()) {
            app(MenuCache::class)->flush();
        }
    }

    public function saved(PageTranslation $pageTranslation)
    {
        $this->flushMenuCache($pageTranslation);
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
