<?php

namespace Modules\Ecommerce\Providers;

use App\Models\User;
use App\Services\MediaService;
use Lunar\Base\OrderReferenceGenerator;
use Lunar\Base\OrderReferenceGeneratorInterface;
use Illuminate\Support\ServiceProvider;
use Modules\Ecommerce\Console\UpgradingLunarRemoveGetCandyTables;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Services\OrderService;
use Modules\Ecommerce\Services\ProductService;

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

    public $singletons = [
        OrderService::class => OrderService::class,
    ];

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

        $this->commands([
            UpgradingLunarRemoveGetCandyTables::class,
        ]);

        User::resolveRelationUsing('products', function ($userModel) {
            return $userModel->belongsToMany(Product::class, 'product_user');
        });

        User::macro('isProductManager', function () {
            return $this->products->isNotEmpty()
                && (
                    ! $this->IsSuperAdministrator
                    && ! $this->IsAdministrator
                );
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(AuthServiceProvider::class);
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
