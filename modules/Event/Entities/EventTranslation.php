<?php

namespace Modules\Event\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'excerpt',
        'locale',
    ];

    protected static function newFactory()
    {
        return \Modules\Event\Database\factories\EventTranslationFactory::new();
    }
}
