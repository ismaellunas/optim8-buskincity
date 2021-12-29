<?php

namespace App\Entities\Menus;

use App\Models\Menu;
use App\Models\MenuItem;

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

    protected $menu = null;
    protected $menuItem = null;
    protected $modelName = MenuItem::class;

    public function __construct(array $attributes = [])
    {
        $model = new $this->modelName();

        foreach ($model->getAttributes() as $attribute => $value) {
            $this->$attribute = $value;
        }

        $this->id = $attributes['id'] ?? null;
        $this->title = $attributes['title'] ?? null;
        $this->type = $attributes['type'] ?? null;
        $this->order = $attributes['order'] ?? null;
        $this->icon = $attributes['icon'] ?? null;
        $this->is_blank = $attributes['is_blank'] ?? false;
        $this->parent_id = $attributes['parent_id'] ?? null;
        $this->menu_id = $attributes['menu_id'] ?? null;
    }

    protected function getEagerLoads(): array
    {
        return [];
    }

    public function getAttributes(): array
    {
        $attributes = [];

        $model = new $this->modelName();

        foreach ($model->getFillable() as $attributeName) {
            $attributes[ $attributeName ] = $this->$attributeName;
        }

        return $attributes;
    }

    protected function getParentModel(): ?Menu
    {
        if ($this->menu == null) {
            $this->menu = $this->getModel()->menu;
        }

        return $this->menu;
    }

    protected function getModel(): ?MenuItem
    {
        if ($this->menuItem == null && $this->id) {
            $this->loadModel();
        }

        return $this->menuItem;
    }

    protected function getLocale(): ?string
    {
        return $this->getParentModel()->locale ?? null;
    }

    protected function loadModel()
    {
        $this->menuItem = $this->modelName::
            when($this->getEagerLoads(), function ($query) {
                $query->with($this->getEagerLoads());
            })
            ->find($this->id);
    }

    public function setModel(MenuItem $menuItem)
    {
        $this->menuItem = $menuItem;
    }

    public function setParentModel(Menu $menu)
    {
        $this->menu = $menu;
    }

    public function getTarget(): ?string
    {
        return $this->getModel()->is_blank ? "_blank" : null;
    }
}
