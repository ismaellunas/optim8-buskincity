<?php

namespace App\Observers;

use App\Entities\Caches\SettingCache;
use App\Models\Media;

class MediaObserver
{
    public function updated(Media $media)
    {
        app(SettingCache::class)->flush();
    }

    public function deleted(Media $media)
    {
        app(SettingCache::class)->flush();
    }
}
