<?php

namespace App\Entities\Menus;

use App\Contracts\MenuInterface;

class PageMenu extends BaseMenu implements MenuInterface
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->page_id = $attributes['page_id'] ?? null;
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
        $locale = $this->getModel()->menu->locale;
        $page = $this->getModel()->page->translateOrDefault($locale);

        return route('frontend.pages.show', [
            'page_translation' => $page->slug,
        ]);
    }
}
