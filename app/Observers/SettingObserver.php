<?php

namespace App\Observers;

use App\Models\Setting;
use App\Services\SettingService;

class SettingObserver
{
    public function saved(Setting $setting)
    {
        app(SettingService::class)->flushCachedSetting();
    }
}
