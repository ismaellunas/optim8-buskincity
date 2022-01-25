<?php

namespace App\Entities\Caches;

use Illuminate\Support\Facades\Cache;
use \Closure;

abstract class BaseCache
{
    protected string $tag = "";

    protected function getTags(): array
    {
        return [
            $this->tag
        ];
    }

    public function remember(
        string $key,
        Closure $callback
    ): mixed {
        return Cache::tags($this->getTags())->rememberForever(
            $key,
            $callback
        );
    }

    public function flush(): bool
    {
        return Cache::tags($this->tag)->flush();
    }
}