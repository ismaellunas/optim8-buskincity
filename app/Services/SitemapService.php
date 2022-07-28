<?php

namespace App\Services;

use App\Services\ModuleService;
use App\Entities\Sitemaps\BaseSitemap;
use App\Entities\Sitemaps\UrlTag;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;

class SitemapService
{
    public function sitemapClasses(): array
    {
        return array_merge([
            \App\Entities\Sitemaps\Page::class,
            \App\Entities\Sitemaps\Post::class,
            \App\Entities\Sitemaps\Category::class,
            \App\Entities\Sitemaps\Performer::class,
        ], $this->moduleSitemapClasses());
    }

    private function moduleSitemapClasses(): array
    {
        $classes = [];
        $modules = app(ModuleService::class)->getModuleListByStatus(true);

        foreach ($modules as $module) {
            $className = '\\Modules\\'.$module->getName().'\\Sitemaps\\Sitemap';
            if (class_exists($className)) {
                $classes[] = $className;
            }
        }

        return $classes;
    }

    public static function sitemapNames(): array
    {
        return array_merge([
            'post',
            'page',
            'category',
            'performer',
        ], self::moduleSitemapNames());
    }

    private static function moduleSitemapNames(): array
    {
        return [
            'space'
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
            return $this->moduleSitemap($locale);
        }

        return new $className($locale);
    }

    private function moduleSitemap(string $locale)
    {
        $modules = app(ModuleService::class)->getModuleListByStatus(true);

        foreach ($modules as $module) {
            $className = '\\Modules\\'.$module->getName().'\\Sitemaps\\Sitemap';

            if (!class_exists($className)) {
                throw new FileNotFoundException($className." is not found.");
            }
        }

        return new $className($locale);
    }
}
