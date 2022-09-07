<?php

namespace Modules\Ecommerce\Entities;

use App\Models\Media;
use GetCandy\FieldTypes\TranslatedText;
use GetCandy\Models\Product as GetCandyProduct;
use Illuminate\Support\Arr;
use Kodeine\Metable\Metable;
use Modules\Ecommerce\Entities\Schedule;

class Product extends GetCandyProduct
{
    use Metable;

    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';

    protected $metaKeyName = 'product_id';

    public function getMetaTable(): string
    {
        return config('getcandy.database.table_prefix').'products_meta';
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function gallery()
    {
        return $this->morphMany(Media::class, 'medially');
    }

    public function eventSchedule()
    {
        return $this->morphOne(Schedule::class, 'schedulable');
    }

    public function productable()
    {
        return $this->morphTo();
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
}
