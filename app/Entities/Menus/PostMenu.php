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
            'menuItemable' => function ($query) {
                $query->select('id', 'locale', 'slug');
            },
            'menu',
        ];
    }

    public function getUrl(): string
    {
        $model = $this->getModel();

        if ($model->isPolymorphicExists) {
            return $this->getTranslatedUrl(
                route('blog.show', [
                    'slug' => $model->menuItemable->slug,
                ])
            );
        }

        return $this->fallbackUrl();
    }
}