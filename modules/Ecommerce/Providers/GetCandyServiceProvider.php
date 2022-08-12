<?php

namespace Modules\Ecommerce\Providers;

use Cartalyst\Converter\Laravel\Facades\Converter;
use GetCandy\Console\Commands\AddonsDiscover;
use GetCandy\Console\Commands\Import\AddressData;
use GetCandy\Console\Commands\MeilisearchSetup;
use GetCandy\Console\Commands\ScoutIndexer;
use GetCandy\Console\InstallGetCandy;
use GetCandy\GetCandyServiceProvider as VendorGetCandyServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
//use Illuminate\Support\ServiceProvider;

class GetCandyServiceProvider extends VendorGetCandyServiceProvider
{
    public function boot(): void
    {
        //$this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Relation::morphMap([
            'product_type' => GetCandy\Models\ProductType::class,
            //'order' => GetCandy\Models\Order::class,
        ]);

        $this->registerObservers();
        $this->registerBlueprintMacros();

        if (!$this->app->environment('testing')) {
            $this->registerStateListeners();
        }

        if ($this->app->runningInConsole()) {
            collect($this->configFiles)->each(function ($config) {
                $this->publishes([
                    "{$this->root}/config/$config.php" => config_path("getcandy/$config.php"),
                ], 'getcandy');
            });

            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations'),
            ], 'getcandy-migrations');

            $this->commands([
                InstallGetCandy::class,
                AddonsDiscover::class,
                MeilisearchSetup::class,
                AddressData::class,
                ScoutIndexer::class,
            ]);
        }

        Arr::macro('permutate', [\GetCandy\Utils\Arr::class, 'permutate']);

        // Handle generator
        Str::macro('handle', function ($string) {
            return Str::slug($string, '_');
        });

        Converter::setMeasurements(
            config('getcandy.shipping.measurements', [])
        );

        Event::listen(
            Login::class,
            [CartSessionAuthListener::class, 'login']
        );

        Event::listen(
            Logout::class,
            [CartSessionAuthListener::class, 'logout']
        );
    }

    /*
    protected function loadMigrationsFrom($paths)
    {
    }
     */
}
