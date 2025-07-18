<?php

namespace App\Providers;

use App\Models\{
    Category,
    ConnectedAccount,
    ErrorLog,
    GlobalOption,
    Media,
    Page,
    Post,
    Role,
    Setting,
    User
};
use App\Policies\{
    CategoryPolicy,
    ConnectedAccountPolicy,
    ErrorLogPolicy,
    GlobalOptionPolicy,
    MediaPolicy,
    PagePolicy,
    PostPolicy,
    RolePolicy,
    SettingPolicy,
    UserPolicy
};
use App\Services\ResetPasswordService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Category::class => CategoryPolicy::class,
        ConnectedAccount::class => ConnectedAccountPolicy::class,
        ErrorLog::class => ErrorLogPolicy::class,
        GlobalOption::class => GlobalOptionPolicy::class,
        Media::class => MediaPolicy::class,
        Page::class => PagePolicy::class,
        Post::class => PostPolicy::class,
        Role::class => RolePolicy::class,
        Setting::class => SettingPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Implicitly grant "Super Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::after(function ($user, $ability) {
            return $user->hasRole('Super Administrator') ? true : null;
        });

        // Customize reset password link based admin and user
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return ResetPasswordService::getResetUrl($user, $token);
        });
    }
}
