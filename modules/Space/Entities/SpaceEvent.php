<?php

namespace Modules\Space\Entities;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;
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
        return \Modules\Space\Database\factories\SpaceEventFactory::new();
    }

    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    public function scopeSearch($query, string $term)
    {
        $query->where('title', 'ILIKE', '%'.$term.'%');
    }

    public function scopeDateRange(Builder $query, array $dates)
    {
        $dates = collect($dates)->filter()->sort()->unique()->values()->all();

        if (count($dates) == 1) {
            return $query->startAndEndDateRange($dates[0]);
        }

        return $query->where(function ($query) use ($dates) {
            $query
                ->startAndEndDateRange($dates[0])
                ->startAndEndDateRange($dates[1], 'or');
        });
    }

    public function scopeStartAndEndDateRange(
        Builder $query,
        string $date,
        string $boolean = 'and'
    ) {
        $query->where((fn ($query) => $query
              ->whereDate('started_at', '<=', $date)
              ->whereDate('ended_at', '>=', $date)
        ), null, null, $boolean);
    }

    public function scopeHasSpace($query, int $spaceId)
    {
        $query->where('space_id', $spaceId);
    }
}
