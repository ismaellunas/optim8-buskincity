<?php

namespace App\Entities\Menus;

use App\Contracts\MenuInterface;

class PostMenu extends BaseMenu implements MenuInterface
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->post_id = $attributes['post_id'] ?? null;
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
            'locale' => $this->getLocale(),
            'slug' => $this->getModel()->post->slug,
        ]);
    }
}