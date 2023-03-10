<?php

namespace App\Observers;

use App\Entities\Caches\SettingCache;
use App\Jobs\DeleteMediaFromStorage;
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

        DeleteMediaFromStorage::dispatch($media->file_name, $media->file_type)
            ->delay(now()->addSeconds(3));
    }
}
