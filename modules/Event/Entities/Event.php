<?php

namespace Modules\Event\Entities;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model implements TranslatableContract
{
    use HasFactory;
    use Translatable;

    protected $dates = [
        'started_at',
        'ended_at',
    ];

    public $translatedAttributes = [
        'description',
        'excerpt',
        'locale',
    ];

    protected $fillable = [
        'address',
        'started_at',
        'ended_at',
    ];

    protected static function newFactory()
    {
        return \Modules\Event\Database\factories\EventFactory::new();
    }

    public function eventable()
    {
        return $this->morphTo();
    }

    public function scopeSearch($query, string $term)
    {
        $query->where('title', 'ILIKE', '%'.$term.'%');
    }
}
