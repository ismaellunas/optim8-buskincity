<?php

namespace Modules\Ecommerce\Providers;

use Cartalyst\Converter\Laravel\Facades\Converter;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Lunar\Console\Commands\AddonsDiscover;
use Lunar\Console\Commands\Import\AddressData;
use Lunar\Console\Commands\ScoutIndexer;
use Lunar\Console\InstallLunar;
use Lunar\Database\State\ConvertProductTypeAttributesToProducts;
use Lunar\Database\State\EnsureBrandsAreUpgraded;
use Lunar\Database\State\EnsureDefaultTaxClassExists;
use Lunar\LunarServiceProvider as VendorLunarServiceProvider;
use Modules\Ecommerce\Console\MigrateGetCandy;

class LunarServiceProvider extends VendorLunarServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('Ecommerce', 'Database/Migrations/lunar'));

        Relation::morphMap([
            'product_type' => Lunar\Models\ProductType::class,
            //'order' => Lunar\Models\Order::class,
        ]);

        $this->registerObservers();
        $this->registerBlueprintMacros();
        $this->registerStateListeners();

        if ($this->app->runningInConsole()) {
            collect($this->configFiles)->each(function ($config) {
                $this->publishes([
                    "{$this->root}/config/$config.php" => config_path("lunar/$config.php"),
                ], 'lunar');
            });

            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations'),
            ], 'lunar.migrations');

            $this->commands([
                InstallLunar::class,
                AddonsDiscover::class,
                AddressData::class,
                ScoutIndexer::class,
                MigrateGetCandy::class,
            ]);
        }

        Arr::macro('permutate', [\Lunar\Utils\Arr::class, 'permutate']);

        // Handle generator
        Str::macro('handle', function ($string) {
            return Str::slug($string, '_');
        });

        Converter::setMeasurements(
            config('lunar.shipping.measurements', [])
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

    protected function registerStateListeners()
    {
        $states = [
            ConvertProductTypeAttributesToProducts::class,
            EnsureDefaultTaxClassExists::class,
            EnsureBrandsAreUpgraded::class,
            //EnsureMediaCollectionsAreRenamed::class,
        ];

        foreach ($states as $state) {
            $class = new $state;

            Event::listen(
                [MigrationsStarted::class],
                [$class, 'prepare']
            );

            Event::listen(
                [MigrationsEnded::class, NoPendingMigrations::class],
                [$class, 'run']
            );
        }
    }
}
