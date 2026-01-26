<?php

namespace Modules\Booking\Entities;

use App\Enums\PublishingStatus;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Booking\Entities\Schedule;
use Modules\Ecommerce\Entities\Product;

class ProductEvent extends Model implements TranslatableContract
{
    use HasFactory;
    use Translatable;

    protected $table = 'product_events';

    protected $translationModel = ProductEventTranslation::class;

    protected $translationForeignKey = 'product_event_id';

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
        'city',
        'country_code',
        'latitude',
        'longitude',
        'started_at',
        'ended_at',
        'status',
        'title',
        'timezone',
    ];

    protected $casts = [
        'latitude' => 'double',
        'longitude' => 'double',
    ];

    protected static function newFactory()
    {
        return \Modules\Booking\Database\factories\ProductEventFactory::new();
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function schedule()
    {
        return $this->morphOne(Schedule::class, 'schedulable');
    }

    public function scopePublished(Builder $query)
    {
        return $query->where('status', PublishingStatus::PUBLISHED->value);
    }

    public function scopeSearch($query, string $term)
    {
        $query->where('title', 'ILIKE', '%'.$term.'%');
    }

    public function getDisplayStatusAttribute(): string
    {
        return PublishingStatus::options()
            ->firstWhere('id', $this->status)['value'] ?? "";
    }
}
