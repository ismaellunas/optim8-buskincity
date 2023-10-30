<?php

namespace Modules\Space\Observers;

use App\Entities\Caches\MenuCache;
use App\Observers\PageTranslationObserver as AppPageTranslationObserver;

class PageTranslationObserver extends AppPageTranslationObserver
{
    protected function flushMenuCache($pageTranslation)
    {
        if ($pageTranslation->isClearingMenuCacheRequired()) {
            app(MenuCache::class)->flush();
        }
    }
}
