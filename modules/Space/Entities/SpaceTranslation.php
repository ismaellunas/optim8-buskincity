<?php

namespace Modules\Space\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SpaceTranslation extends Model
{
    use HasFactory;

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
