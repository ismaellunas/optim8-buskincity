<?php

namespace App\Providers;

use App\Models\{
    Page,
    Post,
    Role,
    Setting,
    User,
};
use App\Observers\{
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
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Page::observe(PageObserver::class);
        Post::observe(PostObserver::class);
        Role::observe(RoleObserver::class);
        Setting::observe(SettingObserver::class);
        User::observe(UserObserver::class);
    }
}
