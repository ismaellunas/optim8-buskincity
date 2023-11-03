<?php

namespace Modules\Space\Providers;

use App\Listeners\SanitizeDisabledComponentsOnPageTranslations;
use App\Listeners\UnassignModulePermissions;
use App\Observers\PageObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Space\Entities\Page;
use Modules\Space\Entities\PageTranslation;
use Modules\Space\Entities\Space;
use Modules\Space\Events\ModuleDeactivated;
use Modules\Space\Listeners\RemoveSpaceFromMenus;
use Modules\Space\Listeners\SetPublishedEventsDrafts;
use Modules\Space\Listeners\SetPublishedPageTranslationsDrafts;
use Modules\Space\Listeners\UnassignAllSpaceManagers;
use Modules\Space\Observers\PageTranslationObserver;
use Modules\Space\Observers\SpaceObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        ModuleDeactivated::class => [
            UnassignModulePermissions::class,
            UnassignAllSpaceManagers::class,
            SetPublishedEventsDrafts::class,
            SetPublishedPageTranslationsDrafts::class,
            RemoveSpaceFromMenus::class,
            SanitizeDisabledComponentsOnPageTranslations::class,
        ],
    ];

    public function boot()
    {
        Page::observe(PageObserver::class);
        PageTranslation::observe(PageTranslationObserver::class);
        Space::observe(SpaceObserver::class);
    }
}
