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

    public $translatedAttributes = ['title'];

    public function saveFromInputs($inputs)
    {
        $this->fill($inputs);
        $this->save();
    }

    public function syncTranslations(array $providedLocales)
    {
        $existingLocales = array_keys($this->getTranslationsArray());

        $unusedLocales = array_diff($existingLocales, $providedLocales);

        if (!empty($unusedLocales)) {
            $this->deleteTranslations($unusedLocales);
        }
    }

    // Relation
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}
