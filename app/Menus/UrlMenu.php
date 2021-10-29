<?php

namespace App\Menus;

use App\Contracts\MenuInterface;

use App\Models\MenuItem;

class UrlMenu implements MenuInterface
{
    public $id;
    private $menuItem;
    private $locale;

    function __construct($locale, $id)
    {
        $this->menuItem = MenuItem::find($id);
        $this->locale = $locale;
    }

    function getTranslation(): object
    {
        return $this->menuItem->translateOrDefault($this->locale);
    }

    function getTitle(): string
    {
        return $this->getTranslation()->title;
    }

    function getUrl(): string
    {
        return $this->menuItem->url ?? "";
    }
}