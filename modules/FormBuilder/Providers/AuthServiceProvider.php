<?php

namespace Modules\FormBuilder\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Modules\FormBuilder\Entities\FieldGroup;
use Modules\FormBuilder\Entities\FieldGroupNotificationSetting;
use Modules\FormBuilder\Policies\FormBuilderPolicy;
use Modules\FormBuilder\Policies\NotificationSettingPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        FieldGroup::class => FormBuilderPolicy::class,
        FieldGroupNotificationSetting::class => NotificationSettingPolicy::class
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
