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
            ->with(['page', 'menu'])
            ->first();
    }

    function getUrl(): string
    {
        $locale = $this->menuItem->locale;
        return route('blog.category.index', [
            'locale' => $this->menuItem->menu->locale,
            'id' => $this->menuItem->category_id,
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