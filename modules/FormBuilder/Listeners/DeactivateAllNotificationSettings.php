<?php

namespace Modules\FormBuilder\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\FormBuilder\Entities\FormNotificationSetting;
use Modules\FormBuilder\Events\ModuleDeactivated;

class DeactivateAllNotificationSettings implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ModuleDeactivated $event)
    {
        FormNotificationSetting::active()
            ->get(['id', 'form_id', 'is_active', 'subject'])
            ->each(function ($notificationSetting) {
                $notificationSetting->is_active = false;
                $notificationSetting->save();
            });
    }
}
