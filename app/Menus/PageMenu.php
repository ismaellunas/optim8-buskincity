<?php

namespace App\Menus;

use App\Contracts\MenuInterface;

use App\Models\MenuItem;

class PageMenu implements MenuInterface
{
    public $id;
    private $menuItem;
    private $locale;

    function __construct($locale, $id)
    {
        $this->menuItem = MenuItem::where('id', $id)
            ->with('page')
            ->first();
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
        $page = $this->menuItem->page->translateOrDefault($this->locale);

        return route('frontend.pages.show', [
            'locale' => $this->locale,
            'page_translation' => $page->slug,
        ]);
    }
}