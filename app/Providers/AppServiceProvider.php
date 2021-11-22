<?php

namespace App\Providers;

use App\Entities\Caches\MenuCache;
use App\Entities\Caches\SettingCache;
use App\Services\{
    MenuService,
    PageBuilderService,
    PageService,
    SettingService,
    TranslationService,
};
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $singletons = [
        MenuCache::class => MenuCache::class,
        SettingCache::class => SettingCache::class,

        MenuService::class => MenuService::class,
        PageBuilderService::class => PageBuilderService::class,
        PageService::class => PageService::class,
        SettingService::class => SettingService::class,
        TranslationService::class => TranslationService::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
