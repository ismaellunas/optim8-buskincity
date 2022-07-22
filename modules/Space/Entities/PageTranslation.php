<?php

namespace Modules\Space\Entities;

use App\Models\PageTranslation as AppPageTranslation;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Modules\Space\Entities\Page;

class PageTranslation extends AppPageTranslation
{
    protected $appends = ['landing_page_space_url'];

    protected static function newFactory()
    {
        return \Modules\Space\Database\factories\PageTranslationFactory::new();
    }

    // Override from Page::class
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
