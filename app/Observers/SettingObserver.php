<?php

namespace App\Observers;

use App\Entities\Caches\SettingCache;

class SettingObserver
{
    public function saved()
    {
        app(SettingCache::class)->flush();
    }
}
