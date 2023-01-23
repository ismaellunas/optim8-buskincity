<?php

namespace Modules\FormBuilder\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Modules\FormBuilder\Entities\Form;
use Modules\FormBuilder\Entities\FormNotificationSetting;
use Modules\FormBuilder\Policies\FormBuilderPolicy;
use Modules\FormBuilder\Policies\NotificationSettingPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Form::class => FormBuilderPolicy::class,
        FormNotificationSetting::class => NotificationSettingPolicy::class
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
