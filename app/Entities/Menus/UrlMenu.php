<?php

namespace App\Entities\Menus;

use App\Contracts\MenuInterface;

class UrlMenu extends BaseMenu implements MenuInterface
{
    public function __construct($menuItem, $locale)
    {
        parent::__construct($menuItem, $locale);

        $this->url = $menuItem->url;
    }

    public function getUrl(): string
    {
        return $this->getModel()->url ?? "";
    }
}