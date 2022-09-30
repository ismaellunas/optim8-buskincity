<?php

namespace App\Observers;

use App\Entities\Caches\GlobalOptionCache;
use App\Models\GlobalOption;

class GlobalOptionObserver
{
    public function saved()
    {
        app(GlobalOptionCache::class)->flush();
    }
}
