<?php

namespace Modules\Space\Menus;

use App\Contracts\MenuBuilderInterface;
use Modules\Space\Entities\Space;
use Illuminate\Support\Str;

class MenuOption implements MenuBuilderInterface
{
    protected $key = 'space';

    public function getKey(): string
    {
        return Str::lower($this->getTypeOptions()['id'] ?? $this->key);
    }

    public function getTypeOptions(): array
    {
        return [
            'id' => 'space',
            'value' => 'Space',
            'model' => 'Modules\Space\Entities\Space',
        ];
    }

    public function getMenuOptions(): array
    {
        return Space::with([
            'page.translations' => function ($query) {
                $query->select([
                    'id',
                    'page_id',
                    'locale',
                    'title',
                ])
                ->published();
            }
        ])
        ->has('page')
        ->get()
        ->map(function ($space) {
            $page = $space->page;
            if (count($page->translations) !== 0) {
                $locales = $page
                    ->translations
                    ->map(function ($translation) {
                        return $translation->locale;
                    });
                return [
                    'id' => $space->id,
                    'value' => $space->name,
                    'locales' => $locales,
                ];
            }
        })
        ->filter(function ($value) {
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