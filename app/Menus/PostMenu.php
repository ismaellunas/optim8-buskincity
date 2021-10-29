<?php

namespace App\Menus;

use App\Contracts\MenuInterface;

use App\Models\MenuItem;

class PostMenu implements MenuInterface
{
    public $id;
    private $menuItem;
    private $locale;

    function __construct($locale, $id)
    {
        $this->menuItem = MenuItem::where('id', $id)
            ->with('post')
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
        return route('blog.show', [
            'locale' => $this->locale,
            'slug' => $this->menuItem->post->slug,
        ]);
    }
}