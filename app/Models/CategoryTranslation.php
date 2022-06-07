<?php

namespace App\Models;

use App\Traits\HasLocale;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoryTranslation extends BaseModel
{
    use HasFactory;
    use HasLocale;

    protected $fillable = [
        'name',
        'slug',
        'meta_title',
        'meta_description',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
