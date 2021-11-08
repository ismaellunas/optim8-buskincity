<?php

namespace App\Entities\Menus;

use App\Contracts\MenuInterface;

use App\Models\MenuItem;

class UrlMenu implements MenuInterface
{
    public $id;
    private $menuItem;

    function __construct($id = null)
    {
        $this->menuItem = MenuItem::find($id);
    }

    function getUrl(): string
    {
        return $this->menuItem->url ?? "";
    }

    function nullFields(): array
    {
        return [
            'page_id',
            'post_id',
            'category_id',
        ];
    }
}