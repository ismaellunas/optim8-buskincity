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
            $query->whereHas('page', function (Builder $query) {
                $query->type(Page::TYPE);
            });
        });
    }

    /**
     * @override
     */
    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function getLandingPageSpaceUrlAttribute(): ?string
    {
        if ($this->slug) {
            return LaravelLocalization::getURLFromRouteNameTranslated(
                $this->locale,
                'frontend.spaces.show',
                [
                    'page_translation' => $this->slug
                ]
            );
        }

        return null;
    }
}
