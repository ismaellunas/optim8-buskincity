<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class MenuItem extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'locale',
        'title',
        'type',
        'url',
        'order',
        'parent_id',
        'menu_id',
        'page_id',
        'post_id',
        'category_id',
    ];

    const TYPE_URL = 1;
    const TYPE_PAGE = 2;
    const TYPE_POST = 3;
    const TYPE_CATEGORY = 4;
    const TYPE_VALUES = [
        self::TYPE_URL => 'Url',
        self::TYPE_PAGE => 'Page',
        self::TYPE_POST => 'Post',
        self::TYPE_CATEGORY => 'Category',
    ];

    public function updateMenuItems(array $inputs, $menuId)
    {
        $this->updateMenuItem($inputs, null, $menuId);
    }

    private function updateMenuItem(
        array $inputs,
        $parentId = null,
        $menuId = null
    ) {
        $order = 1;
        foreach ($inputs as $key => $input) {
            $input = $this->setNullInput($input);
            $input['order'] = $order;
            $input['parent_id'] = $parentId;
            $input['menu_id'] = $menuId;

            $menuItem = self::updateOrCreate([
                'id' => $input['id'],
                'menu_id' => $input['menu_id'],
            ], $input);

            if (count($input['children']) > 0) {
                $this->updateMenuItem($input['children'], $menuItem['id'], $menuId);
            }

            $order++;
        }
    }

    private function setNullInput($input)
    {
        $className = "\App\Entities\Menus\\".MenuItem::TYPE_VALUES[$input['type']]."Menu";
        $menu = new $className();

        foreach ($menu->nullFields() as $nullField) {
            $input[$nullField] = null;
        }

        return $input;
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
