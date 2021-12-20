<?php

namespace App\Entities\Caches;

use Illuminate\Support\Facades\Cache;
use \Closure;

class MenuCache
{
    private string $tag = 'menus';

    private function getCacheKey($key, $locale)
    {
        return $key.':'.$locale;
    }

    public function remember(string $key, Closure $callback, string $locale): mixed
    {
        return Cache::tags($this->tag)->rememberForever(
            $this->getCacheKey($key, $locale),
            $callback
        );
    }

    public function flush(): bool
    {
        return Cache::tags($this->tag)->flush();
    }
}
