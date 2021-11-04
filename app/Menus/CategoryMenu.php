<?php

namespace App\Menus;

use App\Contracts\MenuInterface;

use App\Models\MenuItem;

class CategoryMenu implements MenuInterface
{
    public $id;
    private $menuItem;

    function __construct($id)
    {
        $this->menuItem = MenuItem::where('id', $id)
            ->with('page')
            ->first();
    }

    function getUrl(): string
    {
        return route('category.show', [
            'locale' => $this->menuItem->locale,
            'category' => $this->menuItem->category_id,
        ]);
    }
}