<?php

namespace App\Entities\Menus;

use App\Contracts\MenuInterface;

use App\Models\MenuItem;

class PostMenu implements MenuInterface
{
    public $id;
    private $menuItem;

    function __construct($id = null)
    {
        $this->menuItem = MenuItem::where('id', $id)
            ->with(['post', 'menu'])
            ->first();
    }

    function getUrl(): string
    {
        return route('blog.show', [
            'locale' => $this->menuItem->menu->locale,
            'slug' => $this->menuItem->post->slug,
        ]);
    }

    function nullFields(): array
    {
        return [
            'url',
            'page_id',
            'category_id',
        ];
    }
}