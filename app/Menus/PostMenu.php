<?php

namespace App\Menus;

use App\Contracts\MenuInterface;

use App\Models\MenuItem;

class PostMenu implements MenuInterface
{
    public $id;
    private $menuItem;

    function __construct($id)
    {
        $this->menuItem = MenuItem::where('id', $id)
            ->with('post')
            ->first();
    }

    function getUrl(): string
    {
        return route('blog.show', [
            'locale' => $this->menuItem->locale,
            'slug' => $this->menuItem->post->slug,
        ]);
    }
}