<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\ConnectedAccount;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Policies\CategoryPolicy;
use App\Policies\ConnectedAccountPolicy;
use App\Policies\MediaPolicy;
use App\Policies\PagePolicy;
use App\Policies\PostPolicy;
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
        Media::class => MediaPolicy::class,
        Page::class => PagePolicy::class,
        Post::class => PostPolicy::class,
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
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Administrator') ? true : null;
        });
    }
}
