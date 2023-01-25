<?php

namespace Modules\Space\Entities;

use App\Models\BaseModel;
use App\Traits\HasLocale;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SpaceTranslation extends BaseModel
{
    use HasFactory;
    use HasLocale;

    protected $fillable = [
        'condition',
        'description',
        'excerpt',
        'surface',
    ];

    protected static function newFactory()
    {
        return \Modules\Space\Database\factories\SpaceTranslationFactory::new();
    }
}
