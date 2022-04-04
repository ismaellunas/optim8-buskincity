<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseFormRequest extends FormRequest
{
    final public function attributes(): array
    {
        return array_merge(
            $this->translatedAttributes(),
            $this->customAttributes()
        );
    }

    final protected function translatedAttributes(): array
    {
        $keys = array_keys($this->rules());

        $attributes = [];

        foreach ($keys as $key) {
            $keyTranslation = 'validation.attributes.' . $key;

            if (__($keyTranslation) != $keyTranslation) {
                $attributes[$key] = __('validation.attributes.' . $key);
            }
        }

        return $attributes;
    }

    protected function customAttributes(): array
    {
        return [];
    }
}
