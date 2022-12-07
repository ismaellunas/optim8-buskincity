<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PageTranslationSetting extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'page_translation_id'
    ];
}
