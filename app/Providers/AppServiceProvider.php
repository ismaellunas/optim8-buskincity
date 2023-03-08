<?php

namespace App\Providers;

use App\Contracts\MediaStorageInterface;
use App\Entities\{
    CloudinaryStorage,
    LifetimeCookie,
    SocialiteManager,
};
use App\Entities\Caches\{
    CountryCache,
    GlobalOptionCache,
    MenuCache,
    SettingCache,
    TranslationCache
};
use App\Services\{
    CountryService,
    CategoryService,
    FormService,
    GlobalOptionService,
    IPService,
    LanguageService,
    MediaService,
    MenuService,
    ModuleService,
    PageBuilderService,
    PageService,
    SettingService,
    StripeService,
    StripeSettingService,
    ThemeService,
    TranslationService,
    WidgetService,
};
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;

class AppServiceProvider extends ServiceProvider
{
    public $singletons = [
        LifetimeCookie::class => LifetimeCookie::class,

        CountryCache::class => CountryCache::class,
        GlobalOptionCache::class => GlobalOptionCache::class,
        MenuCache::class => MenuCache::class,
        SettingCache::class => SettingCache::class,
        TranslationCache::class => TranslationCache::class,

        CountryService::class => CountryService::class,
        CategoryService::class => CategoryService::class,
        FormService::class => FormService::class,
        GlobalOptionService::class => GlobalOptionService::class,
        IPService::class => IPService::class,
        LanguageService::class => LanguageService::class,
        MediaService::class => MediaService::class,
        MenuService::class => MenuService::class,
        ModuleService::class => ModuleService::class,
        PageBuilderService::class => PageBuilderService::class,
        PageService::class => PageService::class,
        SettingService::class => SettingService::class,
        StripeService::class => StripeService::class,
        StripeSettingService::class => StripeSettingService::class,
        TranslationService::class => TranslationService::class,
        WidgetService::class => WidgetService::class,

        MediaStorageInterface::class => CloudinaryStorage::class,
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

        $this->app->singleton(ThemeService::class, function ($app) {
            return new ThemeService(
                $app->make(SettingService::class),
                $app->make(MediaService::class)
            );
        });

        $this->app->extend(SocialiteFactory::class, function ($command, $app) {
            return new SocialiteManager($app);
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

        if (env('APP_HTTPS_IS_ON', false)) {
            URL::forceScheme('https');
        }
    }
}
