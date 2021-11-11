<?php

namespace App\Entities\Menus;

use App\Models\MenuItem;

abstract class BaseMenu
{
    public $id;
    public $title;
    public $type;
    public $url;
    public $order;
    public $parent_id;
    public $menu_id;
    public $page_id;
    public $post_id;
    public $category_id;

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

    protected function getModel(): ?MenuItem
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
}
