<?php

namespace App\Entities\Menus;

use App\Contracts\MenuInterface;

class PostMenu extends BaseMenu implements MenuInterface
{
    public function __construct($menuItem, $locale)
    {
        parent::__construct($menuItem, $locale);

        $this->post_id = $menuItem->post_id;
    }

    protected function getEagerLoads(): array
    {
        return [
            'post' => function ($query) {
                $query->select('id', 'locale', 'slug');
            },
            'menu',
        ];
    }

    public function getUrl(): string
    {
        return route('blog.show', [
            'slug' => $this->getModel()->post->slug,
        ]);
    }
}