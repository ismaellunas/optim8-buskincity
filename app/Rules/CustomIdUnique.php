<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CustomIdUnique implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $customIds = collect($value['entities'])
            ->where('componentName', 'Columns')
            ->map(function ($entity) {
                return $entity['config']['wrapper']['customId'] ?? null;
            })
            ->flatten()
            ->filter()
            ->all();

        if (count($customIds) === count(array_unique($customIds))) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('The custom ID must be unique.');
    }
}
