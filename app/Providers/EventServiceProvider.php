<?php

namespace App\Providers;

use App\Models\{
    Post,
    Role,
    Setting,
    User,
};
use App\Observers\{
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
        Post::observe(PostObserver::class);
        Role::observe(RoleObserver::class);
        Setting::observe(SettingObserver::class);
        User::observe(UserObserver::class);
    }
}
