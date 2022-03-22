<?php

namespace App\Providers;

use App\Entities\LifetimeCookie;
use App\Entities\Caches\{
    CountryCache,
    MenuCache,
    SettingCache,
    TranslationCache,
    WidgetCache
};
use App\Services\{
    CountryService,
    FormService,
    IPService,
    LanguageService,
    MediaService,
    MenuService,
    PageBuilderService,
    PageService,
    SettingService,
    StripeService,
    StripeSettingService,
    TranslationService,
    WidgetService,
};
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $singletons = [
        LifetimeCookie::class => LifetimeCookie::class,

        CountryCache::class => CountryCache::class,
        MenuCache::class => MenuCache::class,
        SettingCache::class => SettingCache::class,
        TranslationCache::class => TranslationCache::class,
        WidgetCache::class => WidgetCache::class,

        CountryService::class => CountryService::class,
        FormService::class => FormService::class,
        IPService::class => IPService::class,
        LanguageService::class => LanguageService::class,
        MediaService::class => MediaService::class,
        MenuService::class => MenuService::class,
        PageBuilderService::class => PageBuilderService::class,
        PageService::class => PageService::class,
        SettingService::class => SettingService::class,
        StripeService::class => StripeService::class,
        StripeSettingService::class => StripeSettingService::class,
        TranslationService::class => TranslationService::class,
        WidgetService::class => WidgetService::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(\App\Services\UserProfileService::class, function ($app) {
            return new \App\Services\UserProfileService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        EloquentCollection::macro(
            'asOptions',
            function (string $idKey, string $valueKey) {
                return $this->map(function ($item) use ($idKey, $valueKey) {
                    return [
                        'id' => $item->$idKey,
                        'value' => $item->$valueKey,
                    ];
                });
            }
        );
    }
}
