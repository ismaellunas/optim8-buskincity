<?php

namespace App\Entities\Menus;

use App\Contracts\MenuBuilderInterface;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoryMenuBuilder implements MenuBuilderInterface
{
    protected $key = 'category';

    public function getKey(): string
    {
        return Str::lower($this->getTypeOptions()['id'] ?? $this->key);
    }

    public function getTypeOptions(): array
    {
        return [
            'id' => 'category',
            'value' => 'Category',
            'model' => 'App\Models\Category',
        ];
    }

    public function getMenuOptions(): array
    {
        return Category::with([
                'translations' => function ($query) {
                    $query->select([
                        'id',
                        'category_id',
                        'locale',
                        'name',
                    ]);
                },
            ])
            ->get(['id'])
            ->map(function ($category) {

                $locales = $category
                    ->translations
                    ->map(function ($translation) {
                        return $translation->locale;
                    });

                return [
                    'id' => $category->id,
                    'value' => $category->name ?? $category->translations[0]->name,
                    'locales' => $locales,
                ];
            })
            ->all();
    }

    public function isOptionDisplayed(): bool
    {
        return true;
    }
}