<?php

namespace App\Menus;

use App\Contracts\MenuInterface;

use App\Models\MenuItem;

class UrlMenu implements MenuInterface
{
    public $id;
    private $menuItem;

    function __construct($id)
    {
        $this->menuItem = MenuItem::find($id);
    }

    function getUrl(): string
    {
        return $this->menuItem->url ?? "";
    }
}