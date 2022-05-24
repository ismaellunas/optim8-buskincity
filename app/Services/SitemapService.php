<?php

namespace App\Services;

use App\Entities\Sitemaps\BaseSitemap;
use App\Entities\Sitemaps\UrlTag;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;

class SitemapService
{
    public function sitemapClasses(): array
    {
        return [
            \App\Entities\Sitemaps\Page::class,
            \App\Entities\Sitemaps\Post::class,
            \App\Entities\Sitemaps\Category::class,
        ];
    }

    public function sitemaps(string $locale): array
    {
        $urls = [];

        foreach ($this->sitemapClasses() as $sitemapClass) {
            $sitemap = new $sitemapClass($locale);

            $urls[] = new UrlTag(
                $sitemap->locTag(),
                $sitemap->optionalTags(),
            );
        }

        return $urls;
    }

    public function sitemap(string $sitemapName, string $locale): BaseSitemap
    {
        $className = "\\App\\Entities\\Sitemaps\\".Str::studly($sitemapName);

        if (!class_exists($className)) {
            throw new FileNotFoundException($className." is not found.");
        }

        return new $className($locale);
    }
}
