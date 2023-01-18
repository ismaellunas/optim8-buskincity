<?php

namespace Modules\Space\Sitemaps;

use App\Contracts\SitemapInterface;
use App\Entities\Sitemaps\BaseSitemap;
use App\Entities\Sitemaps\UrlTag;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Space\Entities\PageTranslation;

class Sitemap extends BaseSitemap implements SitemapInterface
{
    /**
     * @override
     */
    protected function getLocName(): string
    {
        return config('space.name');
    }

    public function urls(): array|Collection
    {
        $urls = $this->getEloquentBuilder()
            ->get()
            ->map(function ($pageTranslation) {
                return $this->createUrlTag($pageTranslation);
            })
            ->filter();

        return $urls;
    }

    public function optionalTags(): array
    {
        $lastmod = Carbon::today();
        $latestPageTranslation = $this->getEloquentBuilder()
            ->orderBy('updated_at', 'desc')
            ->first();

        if ($latestPageTranslation) {
            $lastmod = $latestPageTranslation->updated_at;
        }

        return array_merge(
            parent::optionalTags(),
            [
                'lastmod' => $lastmod,
            ]
        );
    }

    private function getEloquentBuilder(): Builder
    {
        return PageTranslation::select([
                'slug',
                'unique_key',
                'updated_at',
                'page_id',
                'locale',
            ])
            ->with(['page'])
            ->whereHas('page.space', function ($query) {
                $query->where('is_page_enabled', true);
            })
            ->inLanguages([$this->locale])
            ->published();
    }

    private function createUrlTag(PageTranslation $pageTranslation): UrlTag
    {
        return new UrlTag(
            $this->locationUrl($pageTranslation->landingPageSpaceUrl),
            ['lastmod' => $pageTranslation->updated_at]
        );
    }
}