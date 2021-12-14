<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class GroupAndKeyTranslation implements Rule, DataAwareRule
{
    protected $data = [];
    protected $keyWithGroups;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($keyWithGroups)
    {
        $this->keyWithGroups = $keyWithGroups;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $index = strtok($attribute, '.');

        $group = $this->data[$index]['group'] ?? null;

        return (
            $group
            && $this->keyWithGroups->has($group)
            && $this->keyWithGroups->get($group)->contains($value)
        );
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('The combination between group and key is invalid.');
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
