<?php

namespace Modules\Space\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SpaceEventTranslation extends Model
{
    use HasFactory;

    protected $table = 'space_event_translations';

    protected $fillable = [
        'description',
        'excerpt',
        'locale',
    ];

    protected static function newFactory()
    {
        return \Modules\Space\Database\factories\EventTranslationFactory::new();
    }
}
