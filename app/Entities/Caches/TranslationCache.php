<?php

namespace App\Entities\Caches;

use Illuminate\Support\Facades\Cache;
use \Closure;

class TranslationCache
{
    private string $tag = 'translation';

    private function getKey(string $locale, string $group): string
    {
        return $locale . ":" . $group;
    }

    private function getLocaleTag(string $locale): string
    {
        return $this->tag . ":" . $locale;
    }

    private function getGroupTag(string $locale, string $group): string
    {
        return $this->tag . ":" . $this->getKey($locale, $group);
    }

    public function remember(string $locale, string $group, Closure $callback): mixed
    {
        $key = $this->getKey($locale, $group);

        return Cache::tags([
            $this->tag,
            $this->getLocaleTag($locale),
            $this->getGroupTag($locale, $group)
        ])->rememberForever(
            $key,
            $callback
        );
    }

    public function flush(): bool
    {
        return Cache::tags($this->tag)->flush();
    }

    public function flushLocale(string $locale): bool
    {
        return Cache::tags($this->getLocaleTag($locale))->flush();
    }

    public function flushGroup(string $locale, string $group): bool
    {
        return Cache::tags($this->getGroupTag($locale, $group))->flush();
    }
}
