<?php

namespace Modules\Booking\Providers;

use App\Services\ModuleService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Modules\Booking\Entities\Event;
use Modules\Booking\Entities\OrderCheckIn;
use Modules\Booking\Policies\OrderPolicyMixin;
use Modules\Booking\Policies\ProductPolicyMixin;
use Modules\Booking\Policies\SpacePolicyMixin;
use Modules\Booking\Services\EventService;
use Modules\Booking\Services\ProductEventService;
use Modules\Booking\Services\SettingService;
use Modules\Ecommerce\Entities\Order;
use Modules\Ecommerce\Entities\OrderLine;
use Modules\Ecommerce\Policies\OrderPolicy;
use Modules\Ecommerce\Policies\ProductPolicy;
use Modules\Space\Policies\SpacePolicy;

class BookingServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Booking';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'booking';

    public $singletons = [
        EventService::class => EventService::class,
        ProductEventService::class => ProductEventService::class,
        SettingService::class => SettingService::class,
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

        Order::resolveRelationUsing('checkIn', function ($order) {
            return $order->hasOne(OrderCheckIn::class);
        });

        Order::macro('hasCheckIn', function () {
            return !is_null($this->checkIn);
        });

        OrderLine::resolveRelationUsing('events', function ($orderLineModel) {
            return $orderLineModel->hasMany(Event::class);
        });

        OrderLine::resolveRelationUsing('latestEvent', function ($orderLineModel) {
            return $orderLineModel->hasOne(Event::class)->latest();
        });

        OrderPolicy::mixin(new OrderPolicyMixin());
        ProductPolicy::mixin(new ProductPolicyMixin());
        SpacePolicy::mixin(new SpacePolicyMixin());

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $checking = function () {
                return app(ModuleService::class)->isModuleActive($this->moduleName);
            };

            $schedule
                ->command('booking-event:status-to-ongoing')
                ->everyMinute()
                ->runInBackground()
                ->when($checking);

            $schedule
                ->command('booking-event:status-to-passed')
                ->everyMinute()
                ->runInBackground()
                ->when($checking);

            $schedule
                ->command('booking-event:email-reminder')
                ->everyTenMinutes()
                ->runInBackground()
                ->when($checking);
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);

        $this->commands([
            \Modules\Booking\Console\EventEmailReminder::class,
            \Modules\Booking\Console\SetEventOngoing::class,
            \Modules\Booking\Console\SetEventPassed::class,
            \Modules\Booking\Console\ReplaceEventCalendarsView::class,
        ]);
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
        $module = $this->moduleNameLower . '_module';
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $module);
            $this->loadJsonTranslationsFrom($langPath);

        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $module);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'Resources/lang'));
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
