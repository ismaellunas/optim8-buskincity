<?php

namespace App\Observers;

use App\Services\SettingService;

class SettingObserver
{
    public function saved()
    {
        app(SettingService::class)->flushCachedSetting();
    }
}
