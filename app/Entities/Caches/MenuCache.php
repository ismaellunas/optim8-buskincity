<?php

namespace App\Entities\Caches;

use \Closure;

class MenuCache extends BaseCache
{
    protected string $tag = 'menus';

    private function getKey($key, $locale)
    {
        return $key.':'.$locale;
    }

    public function remember(
        string $key,
        Closure $callback,
        string $locale = null
    ): mixed {
        $key = $this->getKey($key, $locale);

        return parent::remember($key, $callback);
    }
}
