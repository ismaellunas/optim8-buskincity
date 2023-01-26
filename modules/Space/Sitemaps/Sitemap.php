<?php

namespace Modules\Space\Sitemaps;

use App\Contracts\SitemapInterface;
use App\Entities\Sitemaps\BaseSitemap;
use App\Entities\Sitemaps\UrlTag;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Space\Entities\Space;

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
        $urls = $this
            ->getEloquentBuilder()
            ->get()
            ->map(function ($space) {
                return new UrlTag(
                    $this->locationUrl($space->pageLocalizeURL($this->locale)),
                    ['lastmod' => $this->lastMod($space)]
                );
            })
            ->filter()
            ->sortBy('loc');

        return $urls;
    }

    private function lastMod(Space $space): Carbon
    {
        $modifications = collect([
            $space->updated_at,
            $space->translations->max('updated_at') ?? null,
        ]);

        $translationsMod = (
            $space->page->translations->firstWhere('locale', $this->locale)
            ?? $space->page->translations->firstWhere('locale', defaultLocale())
        );

        $modifications->push($translationsMod->updated_at);

        return $modifications
            ->filter()
            ->max();
    }

    private function getEloquentBuilder(): Builder
    {
        $locales = collect([$this->locale, defaultLocale()])->unique()->all();

        return Space::isPageEnabled()
            ->whereHas('pageTranslations', function ($query) use ($locales) {
                foreach ($locales as $key => $locale) {
                    $methodName = ($key == 0) ? 'where' : 'orWhere';

                    $query->$methodName(function ($query) use ($locale) {
                        $query->where('locale', $locale);
                        $query->published();
                    });
                }
            })
            ->withStructuredUrl($locales)
            ->with([
                'translations' => function ($query) {
                    $query->select('id', 'space_id', 'locale', 'updated_at');
                },
            ]);
    }
}