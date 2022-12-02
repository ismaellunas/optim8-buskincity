<?php

namespace App\Entities\Menus\Options;

use App\Contracts\MenuBuilderInterface;
use App\Models\Page;
use Illuminate\Support\Str;

class PageOption implements MenuBuilderInterface
{
    protected $key = 'page';

    public function getKey(): string
    {
        return Str::lower($this->getTypeOptions()['id'] ?? $this->key);
    }

    public function getTypeOptions(): array
    {
        return [
            'id' => 'page',
            'value' => 'Page',
            'model' => 'App\Models\Page',
        ];
    }

    public function getMenuOptions(): array
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
                        'value' => $page->title ?? $page->translations[0]->title,
                        'locales' => $locales,
                    ];
                }
            })->filter(function ($value) {
                return $value != null;
            })
            ->values()
            ->all();
    }

    public function isOptionDisplayed(): bool
    {
        return true;
    }
}