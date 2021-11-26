<?php

namespace App\Providers;

use App\Entities\Caches\{
    MenuCache,
    SettingCache
};
use App\Services\{
    LanguageService,
    SettingService
};
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $singletons = [
        MenuCache::class => MenuCache::class,
        SettingCache::class => SettingCache::class,
        LanguageService::class => LanguageService::class,
        SettingService::class => SettingService::class,
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

        EloquentCollection::macro('paginate', function(
            int $perPage = 15
        ) {
            $page = LengthAwarePaginator::resolveCurrentPage('page');
            $total = $this->count();

            return new LengthAwarePaginator(
                $this->forPage($page, $perPage),
                $total,
                $perPage,
                $page,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ]
            );
        });
    }
}
