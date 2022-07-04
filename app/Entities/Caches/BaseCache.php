<?php

namespace App\Entities\Caches;

use Illuminate\Support\Facades\Cache;
use Predis\Connection\ConnectionException;
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
        Closure $callback,
        mixed $default = null
    ): mixed {
        try {

            return Cache::tags($this->getTags())->rememberForever(
                $key,
                $callback
            );

        } catch (ConnectionException $e) {

            if (env('DEPLOYMENT')) {
                return $default;
            }

            throw $e;
        }
    }

    public function flush(): bool
    {
        return Cache::tags($this->tag)->flush();
    }
}