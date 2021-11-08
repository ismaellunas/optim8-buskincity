<?php

namespace App\Entities\Menus;

use App\Contracts\MenuInterface;

use App\Models\MenuItem;

class CategoryMenu implements MenuInterface
{
    public $id;
    private $menuItem;

    function __construct($id = null)
    {
        $this->menuItem = MenuItem::where('id', $id)
            ->with('page')
            ->first();
    }

    function getUrl(): string
    {
        $locale = $this->menuItem->locale;
        $categoryTranslation = $this->menuItem->category->translateOrDefault($locale);

        return route('blog.index', [
            'locale' => $locale,
            'category' => $categoryTranslation->slug,
        ]);
    }

    function nullFields(): array
    {
        return [
            'url',
            'page_id',
            'post_id',
        ];
    }
}