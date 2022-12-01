<?php

namespace App\Entities\Menus;

use App\Contracts\MenuInterface;

class CategoryMenu extends BaseMenu implements MenuInterface
{
    public function __construct($menuItem, $locale)
    {
        parent::__construct($menuItem, $locale);

        $this->category_id = $menuItem->category_id;
    }

    protected function getEagerLoads(): array
    {
        return [
            'menuItemable' => function ($query) {
                $query->select('id');
                $query->with('translations', function ($query) {
                    $query->select([
                        'id',
                        'category_id',
                        'locale',
                        'name',
                    ]);
                });
            },
            'menu',
        ];
    }

    public function getUrl(): string
    {
        $model = $this->getModel();

        if ($this->getModel()->isPolymorphicExists) {
            return $this->getTranslatedUrl(
                $this->getModel()->menuItemable->blogTranslatedUrl($this->locale)
            );
        }

        return $this->fallbackUrl();
    }
}