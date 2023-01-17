<?php

namespace Modules\Space\Observers;

use App\Observers\PageTranslationObserver as AppPageTranslationObserver;
use App\Entities\Caches\MenuCache;

class PageTranslationObserver extends AppPageTranslationObserver
{
    public function saved($pageTranslation)
    {
        if ($pageTranslation->isClearingMenuCacheRequired()) {
            app(MenuCache::class)->flush();
        }
    }
}
