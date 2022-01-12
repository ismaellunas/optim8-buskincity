<?php

namespace App\Providers;

use App\Entities\Caches\{
    MenuCache,
    SettingCache,
    TranslationCache
};
use App\Services\{
    FormService,
    LanguageService,
    MediaService,
    MenuService,
    PageBuilderService,
    PageService,
    SettingService,
    TranslationService,
};
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $singletons = [
        MenuCache::class => MenuCache::class,
        SettingCache::class => SettingCache::class,
        TranslationCache::class => TranslationCache::class,

        FormService::class => FormService::class,
        LanguageService::class => LanguageService::class,
        MediaService::class => MediaService::class,
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
