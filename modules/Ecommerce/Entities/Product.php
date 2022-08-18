<?php

namespace Modules\Ecommerce\Entities;

use GetCandy\FieldTypes\TranslatedText;
use GetCandy\Models\Product as GetCandyProduct;
use Illuminate\Support\Arr;

class Product extends GetCandyProduct
{
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
