<?php

namespace Modules\FormBuilder\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\FormBuilder\Services\AutomateUserCreationService;
use Modules\FormBuilder\Services\FormBuilderService;

class FormBuilderServiceProvider extends ServiceProvider
{
    public $singletons = [
        AutomateUserCreationService::class => AutomateUserCreationService::class,
        FormBuilderService::class => FormBuilderService::class,
    ];

    /**
     * @var string $moduleName
     */
    protected $moduleName = 'FormBuilder';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'formbuilder';

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

        Blade::componentNamespace('\\Modules\\FormBuilder\\View\\Components', 'formbuilder');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(AuthServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(ShortcodesServiceProvider::class);
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
     * Converts a PascalCase or camelCase string to snake_case.
     *
     * Example:
     *   "FormBuilder" => "form_builder"
     *   "HTMLParser"  => "html_parser"
     *
     * @param string $input The input string in PascalCase or camelCase.
     * @return string The converted string in snake_case.
     */
    function snake_case($input) {
        // Insert underscore before each uppercase letter (except the first), then convert to lowercase
        $pattern = '/(?<!^)[A-Z]/';
        return strtolower(preg_replace($pattern, '_$0', $input));
    }


    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $moduleNameSnake = $this->snake_case($this->moduleName);
        $langPath = resource_path('lang/modules/' . $moduleNameSnake);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $moduleNameSnake);
            $this->loadJsonTranslationsFrom($langPath, $moduleNameSnake);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
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
