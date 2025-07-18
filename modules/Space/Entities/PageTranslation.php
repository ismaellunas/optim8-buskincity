<?php

namespace Modules\Space\Entities;

use App\Models\PageTranslation as AppPageTranslation;
use Illuminate\Database\Eloquent\Builder;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Modules\Space\Entities\Page;

class PageTranslation extends AppPageTranslation
{
    protected static function newFactory()
    {
        return \Modules\Space\Database\factories\PageTranslationFactory::new();
    }

    protected static function booted()
    {
        static::addGlobalScope('spacePageTranslation', function (Builder $query) {
            $query->type(Page::TYPE);
        });
    }

    public function space()
    {
        return $this->hasOneThrough(
            Space::class,
            Page::class,
            'id',
            'page_id',
            'page_id',
            'id'
        );
    }

    /**
     * @override
     */
    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function getSlugs(): string
    {
        $defaultLocale = defaultLocale();

        $space = $this->space;

        $ancestors = $space->ancestors->toFlatTree();

        $pageTranslations = $ancestors
            ->pluck('pageTranslations')
            ->filter();

        $pageTranslations->push($space->pageTranslations);

        if ($pageTranslations->isNotEmpty()) {
            return $pageTranslations
                ->map(function ($translations) use ($defaultLocale) {
                    $localeTranslation =
                        $translations->firstWhere('locale', $this->locale)
                        ?? $translations->firstWhere('locale', $defaultLocale);

                    return $localeTranslation->slug ?? $localeTranslation->uniqueKey;
                })->implode('/');
        }

        return null;
    }

    public function getLandingPageSpaceUrlAttribute(): ?string
    {
        $slugs = $this->getSlugs();

        if (!is_null($slugs)) {
            return LaravelLocalization::getURLFromRouteNameTranslated(
                $this->locale,
                'frontend.spaces.show',
                [
                    'slugs' => $slugs,
                ]
            );
        }

        return null;
    }

    public function isClearingMenuCacheRequired(): bool
    {
        $space = $this->page->space;

        if ($space && $space->menuItems->isNotEmpty()) {
            return collect($this->getChanges())
                ->keys()
                ->contains(fn ($attribute) => in_array($attribute, [
                    'title',
                    'slug',
                    'status',
                ]));
        }

        return false;
    }
}
