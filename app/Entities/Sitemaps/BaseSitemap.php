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
        $name = (new \ReflectionClass($this))->getShortName();

        return route('sitemap.urls', [Str::slug($name)]);
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

    abstract public function urls(): array|Collection;
}
