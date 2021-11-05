<?php

namespace App\Menus;

use App\Contracts\MenuInterface;

use App\Models\MenuItem;

class PageMenu implements MenuInterface
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
        $locale = $this->menuItem->locale;
        $page = $this->menuItem->page->translateOrDefault($locale);

        return route('frontend.pages.show', [
            'locale' => $locale,
            'page_translation' => $page->slug,
        ]);
    }
}