<?php

namespace App\Providers;

use App\Events\RecaptchaError;
use App\Listeners\SendRecaptchaErrorNotification;
use App\Models\{
    Category,
    GlobalOption,
    Page,
    Post,
    Role,
    Setting,
    User,
};
use App\Observers\{
    CategoryObserver,
    GlobalOptionObserver,
    PageObserver,
    PostObserver,
    RoleObserver,
    SettingObserver,
    UserObserver,
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
        RecaptchaError::class => [
            SendRecaptchaErrorNotification::class,
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
        Post::observe(PostObserver::class);
        Role::observe(RoleObserver::class);
        Setting::observe(SettingObserver::class);
        User::observe(UserObserver::class);
    }
}
