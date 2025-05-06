<?php

namespace Modules\Ecommerce\Entities;

use App\Models\Media;
use App\Models\User;
use App\Services\CountryService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Kodeine\Metable\Metable;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Product as LunarProduct;
use Modules\Booking\Entities\Schedule;
use Modules\Ecommerce\Enums\ProductStatus;

class Product extends LunarProduct
{
    use Metable;

    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';

    protected $metaKeyName = 'product_id';

    public function getMetaTable(): string
    {
        return config('lunar.database.table_prefix').'products_meta';
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function gallery()
    {
        return $this->morphToMany(Media::class, 'mediable')
            ->withTimestamps();
    }

    public function eventSchedule()
    {
        return $this->morphOne(Schedule::class, 'schedulable');
    }

    public function productable()
    {
        return $this->morphTo();
    }

    public function managers()
    {
        return $this->belongsToMany(User::class, 'product_user')
            ->withPivot(['user_id']);
    }

    public function scopePublished($query)
    {
        return $query->where('status', ProductStatus::PUBLISHED);
    }

    public function scopeSearchWithoutScout($query, string $term)
    {
        $locale = config('app.locale');

        return $query
            ->where("attribute_data->name->value->{$locale}", 'ILIKE', "%{$term}%")
            ->orWhere("attribute_data->description->value->{$locale}", 'ILIKE', "%{$term}%")
            ->orWhere("attribute_data->short_description->value->{$locale}", 'ILIKE', "%{$term}%")
            ->orWhere(function ($query) use ($term) {
                $query->city($term);
            });
    }

    public function scopeOrderByColumn($query, array $orderConfig)
    {
        $column = $orderConfig['column'] ?? null;
        $order = $orderConfig['order'] ?? 'asc';

        switch ($column) {
            case 'name':
                return $query->orderByName($order);
                break;

            default:
                return $query->orderBy('id', 'DESC');
                break;
        }
    }

    public function scopeOrderByName($query, string $order)
    {
        $locale = config('app.locale');

        return $query
            ->orderBy("attribute_data->name->value->{$locale}", $order);
    }

    public function scopeInStatus($query, array $status)
    {
        return $query->whereIn('status', $status);
    }

    public function scopeCity($query, ?string $city = null)
    {
        return $query->whereHas('metas', function ($q) use ($city) {
            $q->where('key', 'locations');
            $q->where(DB::raw("value::json->0->>'city'"), "ILIKE", $city);
        });
    }

    public function scopeCountry($query, ?string $country = null)
    {
        return $query->whereHas('metas', function ($q) use ($country) {
            $q->where('key', 'locations');
            $q->where(DB::raw("value::json->0->>'country_code'"), "ILIKE", $country);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getSearchableAttributes()
    {
        $attributes = $this->getAttributes();

        $data = Arr::except($attributes, 'attribute_data');

        foreach ($this->attribute_data ?? [] as $field => $value) {
            if ($value instanceof TranslatedText) {
                foreach ($value->getValue() as $locale => $text) {
                    $data[$field.'_'.$locale] = $text?->getValue();
                }
            } else {
                $data[$field] = $this->translateAttribute($field);
            }
        }

        return $data;
    }

    public function getCoverThumbnailUrl(): ?string
    {
        if ($this->gallery->isNotEmpty()) {
            return $this->gallery->first()->thumbnailUrl;
        }

        return null;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->translateAttribute('name', config('app.locale'));
    }

    public function getDisplayShortDescriptionAttribute(): string
    {
        return $this->translateAttribute('short_description', config('app.locale'));
    }

    public function getDisplayDescriptionAttribute(): string
    {
        return $this->translateAttribute('description', config('app.locale'));
    }

    public function syncMedia(array $mediaIds = []): void
    {
        $mediaIds = collect($mediaIds)
            ->filter()
            ->map(fn($mediaId) => (int)$mediaId)
            ->all();

        if (!empty($mediaIds)) {
            $this->gallery()->sync($mediaIds);
        } else {
            $this->detachGallery();
        }
    }

    public function detachGallery(?int $mediaId = null): void
    {
        $this->gallery()->detach($mediaId);
    }

    public function getLocationAttribute()
    {
        $city = $this->locations[0]['city'] ?? null;
        $countryCode = $this->locations[0]['country_code'] ?? null;
        $countryName = null;

        if ($countryCode) {
            $countryName = app(CountryService::class)->getCountryName($countryCode);
        }

        return $city
            ? $city . ($countryName ? ', ' . $countryName : null)
            : $countryName;
    }
}
