<?php

namespace Modules\Space\Services;

use Modules\Space\Entities\Page;

class PageService
{
    public function getPageOptions(): array
    {
        return Page::with([
            'translations' => function ($query) {
                $query->select([
                    'id',
                    'page_id',
                    'locale',
                    'title',
                ])
                ->published();
            },
        ])
        ->get(['id'])
        ->map(function ($page) {
            if (count($page->translations) !== 0) {
                $locales = $page
                    ->translations
                    ->map(function ($translation) {
                        return $translation->locale;
                    });
                return [
                    'id' => $page->id,
                    'value' => '[Space] ' . $page->title ?? $page->translations[0]->title,
                    'locales' => $locales,
                ];
            }
        })->filter(function ($value) {
            return $value != null;
        })
        ->values()
        ->all();
    }
}
