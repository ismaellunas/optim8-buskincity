<?php

namespace Modules\Ecommerce\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Policies\ProductPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Product::class => ProductPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
