<?php

namespace Modules\Space\Providers;

use App\Observers\PageObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Space\Entities\Page;
use Modules\Space\Entities\PageTranslation;
use Modules\Space\Entities\Space;
use Modules\Space\Observers\PageTranslationObserver;
use Modules\Space\Observers\SpaceObserver;

class EventServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Page::observe(PageObserver::class);
        PageTranslation::observe(PageTranslationObserver::class);
        Space::observe(SpaceObserver::class);
    }
}
