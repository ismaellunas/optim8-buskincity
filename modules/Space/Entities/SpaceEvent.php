<?php

namespace Modules\Space\Entities;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Space\Entities\SpaceEventTranslation;

class SpaceEvent extends Model implements TranslatableContract
{
    use HasFactory;
    use Translatable;

    protected $translationModel = SpaceEventTranslation::class;

    protected $table = 'space_events';

    protected $translationForeignKey = 'space_event_id';

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
        return \Modules\Space\Database\factories\EventFactory::new();
    }

    public function eventable()
    {
        return $this->morphTo();
    }

    public function scopeSearch($query, string $term)
    {
        $query->where('title', 'ILIKE', '%'.$term.'%');
    }

    public function scopeHasSpace($query, int $spaceId)
    {
        $query->whereHasMorph(
            'eventable',
            Space::class,
            function ($query) use ($spaceId) {
                $query->where('id', $spaceId);
            }
        );
    }
}
