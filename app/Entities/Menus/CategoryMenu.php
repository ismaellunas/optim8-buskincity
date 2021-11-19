<?php

namespace App\Entities\Menus;

use App\Contracts\MenuInterface;

class CategoryMenu extends BaseMenu implements MenuInterface
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->category_id = $attributes['category_id'] ?? null;
    }

    protected function getEagerLoads(): array
    {
        return [
            'category' => function ($query) {
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
        return route('blog.category.index', [
            'locale' => $this->getLocale(),
            'id' => $this->getModel()->category_id,
        ]);
    }
}