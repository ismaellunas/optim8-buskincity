<?php

namespace Modules\Space\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Modules\Space\Entities\Space;
use Modules\Space\Policies\SpacePolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Space::class => SpacePolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
