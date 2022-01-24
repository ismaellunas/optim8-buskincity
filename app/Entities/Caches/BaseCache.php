<?php

namespace App\Entities\Caches;

use Illuminate\Support\Facades\Cache;
use \Closure;

abstract class BaseCache
{
    protected string $tag = "";

    public function remember(string $key, Closure $callback): mixed
    {
        return Cache::tags($this->tag)->rememberForever(
            $key,
            $callback
        );
    }

    public function flush(): bool
    {
        return Cache::tags($this->tag)->flush();
    }
}