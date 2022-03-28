<?php

namespace App\Entities\Caches;

use Illuminate\Support\Facades\Cache;
use \Closure;

class TranslationCache extends BaseCache
{
    protected string $tag = 'translation';
    private string $locale;
    private string $group;

    private function getKey(string $locale, string $group = null): string
    {
        if (!$group || $group == '') {
            $group = '*';
        }

        return $locale . ":" . $group;
    }

    private function getLocaleTag(string $locale): string
    {
        return $this->tag . ":" . $locale;
    }

    private function getGroupTag(string $locale, string $group = null): string
    {
        return $this->tag . ":" . $this->getKey($locale, $group);
    }

    protected function getTags(): array
    {
        return array_merge(
            parent::getTags(),
            [
                $this->getLocaleTag($this->locale),
                $this->getGroupTag($this->locale, $this->group)
            ]
        );
    }

    public function remember(
        string $locale,
        Closure $callback,
        string $group = null
    ): mixed {
        $key = $this->getKey($locale, $group);

        $this->locale = $locale;
        $this->group = $group;

        return parent::remember($key, $callback);
    }

    public function flushLocale(string $locale): bool
    {
        return Cache::tags($this->getLocaleTag($locale))->flush();
    }

    public function flushGroup(string $locale, string $group): bool
    {
        return Cache::tags($this->getGroupTag($locale, $group))->flush();
    }

    public function flushStringGroup(string $locale): bool
    {
        return Cache::tags($this->getGroupTag($locale))->flush();
    }
}
