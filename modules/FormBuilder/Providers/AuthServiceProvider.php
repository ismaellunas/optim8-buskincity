<?php

namespace Modules\FormBuilder\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Modules\FormBuilder\Entities\FieldGroup;
use Modules\FormBuilder\Policies\FormBuilderPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        FieldGroup::class => FormBuilderPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
