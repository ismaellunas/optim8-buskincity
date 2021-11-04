<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

{
    use HasFactory;

    protected $fillable = [
        'type',
        'url',
        'order',
        'parent_id',
        'menu_id',
        'page_id',
        'post_id',
        'category_id',
    ];

    const TYPE_URL = 'Url';
    const TYPE_PAGE = 'Page';
    const TYPE_POST = 'Post';
    const TYPE_CATEGORY = 'Category';
    const TYPES = [
        self::TYPE_URL,
        self::TYPE_PAGE,
        self::TYPE_POST,
        self::TYPE_CATEGORY,
    ];

    public function saveFromInputs($inputs)
    {
        $this->fill($inputs);
        $this->save();
    }

    {
        }
    }

    public function updateFormatMenuItems(array $inputs)
    {
        $this->updateMenuItemData($inputs);
    }

    private function updateMenuItemData(array $menuItems, $parentId = null)
    {
        $order = 1;

        foreach ($menuItems as $menuItem) {
            if (count($menuItem['children']) > 0) {
                $this->updateMenuItemData($menuItem['children'], $menuItem['id']);
            }

            $menuItem = $this->where('id', $menuItem['id'])->first();
            $menuItem->order = $order;
            $menuItem->parent_id = $parentId;
            $menuItem->save();

            $order++;
        }
    }

    // Relation
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function page()
    {
        return $this->belongsTo(Page::class, 'page_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
