<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class MenuItem extends BaseModel
{
    use HasFactory;

    protected $fillable = [
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

    protected $attributes = [
        'category_id' => null,
        'page_id' => null,
        'parent_id' => null,
        'post_id' => null,
        'url' => null,
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
