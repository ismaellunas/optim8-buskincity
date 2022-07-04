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

    public function rememberForLocale(
        string $key,
        Closure $callback,
        string $locale = null
    ): mixed {
        $key = $this->getKey($key, $locale);

        return $this->remember($key, $callback);
    }
}
