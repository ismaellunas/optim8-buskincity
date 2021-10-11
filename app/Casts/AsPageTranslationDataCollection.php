<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Collection;

class AsPageTranslationDataCollection implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return object|string
     */
    public static function castUsing(array $arguments)
    {
        return new class implements CastsAttributes
        {
            public function get($model, $key, $value, $attributes)
            {
                $result = isset($attributes[$key]) ? new Collection(json_decode($attributes[$key], true)) : null;

                if (! is_null($result)) {
                    if (empty($result->get('entities'))) {
                        $result->put('entities', new \stdClass());
                    }
                }

                return $result;
            }

            public function set($model, $key, $value, $attributes)
            {
                if (
                    $key == 'data'
                    && !is_a($value, Collection::class)
                    && is_array($value)
                    && array_key_exists('entities', $value)
                    && empty($value['entities'])
                ) {
                    $value['entities'] = new \stdClass();
                }

                return [$key => json_encode($value)];
            }
        };
    }
}
