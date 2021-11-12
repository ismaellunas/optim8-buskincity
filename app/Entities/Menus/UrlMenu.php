<?php

namespace App\Entities\Menus;

use App\Contracts\MenuInterface;

class UrlMenu extends BaseMenu implements MenuInterface
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->url = $attributes['url'] ?? null;
    }

    public function getUrl(): string
    {
        return $this->getModel()->url ?? "";
    }
}