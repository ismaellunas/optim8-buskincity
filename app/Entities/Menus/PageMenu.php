<?php

namespace App\Entities\Menus;

use App\Contracts\MenuInterface;

class PageMenu extends BaseMenu implements MenuInterface
{
    public function __construct($menuItem, $locale)
    {
        parent::__construct($menuItem, $locale);

        $this->page_id = $menuItem->page_id;
    }

    protected function getEagerLoads(): array
    {
        return [
            'page' => function ($query) {
                $query->select('id');
                $query->with('translations', function ($query) {
                    $query->select([
                        'id',
                        'page_id',
                        'locale',
                        'slug',
                    ]);
                });
            },
            'menu',
        ];
    }

    public function getUrl(): string
    {
        $page = $this->getModel()->page->translateOrDefault($this->locale);

        return route('frontend.pages.show', [
            'page_translation' => $page->slug,
        ]);
    }
}
