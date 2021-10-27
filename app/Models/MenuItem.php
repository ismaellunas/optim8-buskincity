<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MenuItem extends BaseModel implements TranslatableContract
{
    use HasFactory;
    use Translatable;

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

    public $translatedAttributes = ['title'];

    // Relation
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}
