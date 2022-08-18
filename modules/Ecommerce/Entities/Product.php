<?php

namespace Modules\Ecommerce\Entities;

use GetCandy\FieldTypes\TranslatedText;
use GetCandy\Models\Product as GetCandyProduct;
use Illuminate\Support\Arr;
use Kodeine\Metable\Metable;

class Product extends GetCandyProduct
{
    use Metable;

    protected $metaKeyName = 'product_id';

    public function getMetaTable(): string
    {
        return config('getcandy.database.table_prefix').'products_meta';
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
