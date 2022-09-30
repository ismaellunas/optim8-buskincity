<?php

namespace App\Traits;

trait HasCache
{
    protected $caches = [];

    protected function hasLoadedKey($key): bool
    {
        return array_key_exists($key, $this->caches);
    }

    protected function setLoadedKey($key, $value)
    {
        $this->caches[$key] = $value;
    }

    protected function getLoadedKey($key): mixed
    {
        return $this->caches[$key];
    }
}
