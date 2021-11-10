<?php

namespace App\Entities\Menus;

use App\Contracts\MenuInterface;

use App\Models\MenuItem;

class PageMenu implements MenuInterface
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
        $locale = $this->menuItem->menu->locale;
        $page = $this->menuItem->page->translateOrDefault($locale);

        return route('frontend.pages.show', [
            'locale' => $locale,
            'page_translation' => $page->slug,
        ]);
    }

    function nullFields(): array
    {
        return [
            'url',
            'post_id',
            'category_id',
        ];
    }
}
