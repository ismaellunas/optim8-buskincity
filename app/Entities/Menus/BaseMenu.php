<?php

namespace App\Entities\Menus;

use App\Models\MenuItem;
use Illuminate\Support\Str;

abstract class BaseMenu
{
    public $id;
    public $title;
    public $type;
    public $url;
    public $order;
    public $icon;
    public $is_blank;
    public $parent_id;
    public $menu_id;
    public $page_id;
    public $post_id;
    public $category_id;
    public $children;

    protected $locale;
    protected $menuItem = null;
    protected $modelName = MenuItem::class;

    public function __construct($menuItem, $locale)
    {
        $this->locale = $locale;
        $this->menuItem = $menuItem;

        foreach ($menuItem->getAttributes() as $attribute => $value) {
            $this->$attribute = $value;
        }
    }

    protected function getEagerLoads(): array
    {
        return [];
    }

    public function getModel(): ?MenuItem
    {
        if ($this->menuItem == null && $this->id) {
            $this->loadModel();
        }

        return $this->menuItem;
    }

    protected function loadModel()
    {
        $this->menuItem = $this->modelName::
            when($this->getEagerLoads(), function ($query) {
                $query->with($this->getEagerLoads());
            })
            ->find($this->id);
    }

    abstract public function getUrl();

    public function getTarget(): ?string
    {
        return $this->getModel()->is_blank ? "_blank" : null;
    }

    public function isInternalLink(): bool
    {
        return Str::startsWith($this->getUrl(), config('app.url'));
    }

    public function isActive(string $url): bool
    {
        return $this->getUrl() == $url;
    }
}
