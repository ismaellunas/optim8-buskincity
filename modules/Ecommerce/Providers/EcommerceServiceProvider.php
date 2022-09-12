<?php

namespace Modules\Ecommerce\Providers;

use App\Models\User;
use App\Services\MediaService;
use GetCandy\Base\OrderReferenceGenerator;
use GetCandy\Base\OrderReferenceGeneratorInterface;
use GetCandy\Models\OrderLine;
use Illuminate\Support\ServiceProvider;
use Modules\Ecommerce\Entities\Event;
use Modules\Ecommerce\Services\ProductService;
use Modules\Space\Entities\Space;

class EcommerceServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Ecommerce';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'ecommerce';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        User::resolveRelationUsing('managedSpaceProducts', function ($userModel) {
            return $userModel->belongsToMany(Space::class, 'space_product_managers');
        });

        OrderLine::resolveRelationUsing('events', function ($orderLine) {
            return $orderLine->hasMany(Event::class);
        });

        OrderLine::resolveRelationUsing('latestEvent', function ($orderLine) {
            return $orderLine
                ->hasOne(Event::class)
                ->latest();
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);

        $this->app->singleton(ProductService::class, function ($app) {
            return new ProductService($app->make(MediaService::class));
        });

        $this->app->singleton(OrderReferenceGeneratorInterface::class, function () {
            return new OrderReferenceGenerator();
        });
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}
