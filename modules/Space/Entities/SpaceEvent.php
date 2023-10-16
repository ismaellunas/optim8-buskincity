<?php

namespace Modules\Space\Entities;

use App\Enums\PublishingStatus;
use App\Services\CountryService;
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
        'status',
    ];

    protected $casts = [
        'latitude' => 'double',
        'longitude' => 'double',
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

    public function getFullAddressAttribute(): string
    {
        if ($this->is_same_address_as_parent) {
            return $this->space->fullAddress;
        }

        return collect([
            $this->address,
            $this->city,
            (
                $this->country_code
                ? app(CountryService::class)->getCountryName($this->country_code)
                : null
            ),
        ])->filter()->implode(', ');
    }

    public function scopePublished(Builder $query)
    {
        return $query->where('status', PublishingStatus::PUBLISHED->value);
    }

    public function getDisplayStatusAttribute(): string
    {
        return PublishingStatus::options()
            ->firstWhere('id', $this->status)['value'] ?? "";
    }

    public function setAsDraft(): void
    {
        $this->status = PublishingStatus::DRAFT->value;
        $this->save();
    }
}
