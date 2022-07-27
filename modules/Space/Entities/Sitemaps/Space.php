<?php

namespace Modules\Space\Entities\Sitemaps;

use App\Entities\Sitemaps\BaseSitemap;
use App\Entities\Sitemaps\UrlTag;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Space\Entities\Page;
use Modules\Space\Entities\PageTranslation;

class Space extends BaseSitemap
{
    public function urls(): array|Collection
    {
        $urls = $this->getEloquentBuilder()
            ->orderBy('slug')
            ->get()
            ->map(function ($pageTranslation) {
                if (!$pageTranslation->page->isHomePage) {
                    return $this->createUrlTag($pageTranslation);
                }
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
                'updated_at',
                'page_id',
            ])
            ->with(['page'])
            ->inLanguages([$this->locale])
            ->published()
            ->whereHas('page', function (Builder $query) {
                $query->type(Page::TYPE);
            });
    }

    private function createUrlTag(PageTranslation $pageTranslation): UrlTag
    {
        return new UrlTag(
            $this->locationUrl(route('frontend.spaces.show', [$pageTranslation->slug])),
            ['lastmod' => $pageTranslation->updated_at]
        );
    }
}