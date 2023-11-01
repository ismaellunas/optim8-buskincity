<?php

namespace Modules\Space\Providers;

use App\Models\User;
use App\Services\MediaService;
use Illuminate\Support\ServiceProvider;
use Modules\Space\Entities\Space;
use Modules\Space\Services\PageSpaceService;
use Modules\Space\Services\SpaceEventService;
use Modules\Space\Services\SpaceService;

class SpaceServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Space';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'space';

    /**
     * Boot the application events.
     *
     * @return void
     */

    public $singletons = [
        PageSpaceService::class => PageSpaceService::class,
        SpaceEventService::class => SpaceEventService::class,
    ];

    public function boot()
    {
        Space::disableAutoloadTranslations();

        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        User::resolveRelationUsing('spaces', function ($userModel) {
            return $userModel
                ->belongsToMany(Space::class)
                ->addSelect(Space::getTableName().'.*')
                ->withDepth()
            ;
        });

        User::macro('isSpaceManagerOnlyAccess', function () {
            return $this->spaces->isNotEmpty()
                && ! $this->can('space.viewAny')
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
        $this->app->register(RouteServiceProvider::class);

        $this->app->singleton(SpaceService::class, function ($app) {
            return new SpaceService($app->make(MediaService::class));
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
