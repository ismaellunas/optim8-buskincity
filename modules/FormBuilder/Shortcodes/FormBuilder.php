<?php

namespace Modules\FormBuilder\Shortcodes;

use Illuminate\Support\Str;

class FormBuilder
{
    public function register($shortcode)
    {
        $formIdAttribute = null;
        $attributes = $shortcode->toArray();

        foreach ($attributes as $attribute) {
            if (Str::startsWith($attribute, 'form-id')) {
                $formIdAttribute = $attribute;
            }
        }

        return sprintf(
            '<form-builder %s></form-builder>',
            $formIdAttribute
        );
    }
}
