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

    public function updateMenuItems(array $inputs)
    {
        foreach ($inputs as $key => $input) {
            $this->updateMenuItem($input);
        }
    }

    private function updateMenuItem(array $inputs, $parentId = null)
    {
        $order = 1;
        foreach ($inputs as $input) {
            $input = $this->setNullInput($input);
            $input['order'] = $order;
            $input['parent_id'] = $parentId;

            $menuItem = self::updateOrCreate([
                'id' => $input['id'],
                'locale' => $input['locale'],
                'menu_id' => $input['menu_id'],
            ], $input);

            if (count($input['children']) > 0) {
                $this->updateMenuItem($input['children'], $menuItem['id']);
            }

            $order++;
        }
    }

    private function setNullInput($input)
    {
        $className = "\App\Entities\Menus\\".$input['type']."Menu";
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
