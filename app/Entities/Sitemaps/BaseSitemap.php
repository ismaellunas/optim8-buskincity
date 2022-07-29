<?php

namespace App\Entities\Sitemaps;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class BaseSitemap
{
    protected ?string $locale;

    public function __construct(string $locale)
    {
        $this->locale = $locale;
    }

    public function locTag()
    {
        return route('sitemap.urls', [
            Str::slug($this->getLocName())
        ]);
    }

    public function optionalTags(): array
    {
        return [
            'lastmod' => Carbon::today(),
        ];
    }

    public function locationUrl(string $url): string
    {
        if (! Str::endsWith($url, '/')) {
            return $url.'/';
        }

        return $url;
    }

    protected function getLocName(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    abstract public function urls(): array|Collection;
}
