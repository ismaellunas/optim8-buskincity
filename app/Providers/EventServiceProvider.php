<?php

namespace App\Providers;

use App\Events\ErrorReport;
use App\Listeners\SendErrorReportNotification;
use App\Models\{
    Category,
    GlobalOption,
    Page,
    PageTranslation,
    Post,
    Setting,
};
use App\Observers\{
    CategoryObserver,
    GlobalOptionObserver,
    PageObserver,
    PageTranslationObserver,
    PostObserver,
    SettingObserver,
};
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        ErrorReport::class => [
            SendErrorReportNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Category::observe(CategoryObserver::class);
        GlobalOption::observe(GlobalOptionObserver::class);
        Page::observe(PageObserver::class);
        PageTranslation::observe(PageTranslationObserver::class);
        Post::observe(PostObserver::class);
        Setting::observe(SettingObserver::class);
    }
}
