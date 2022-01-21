<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class FieldMaxFile implements Rule, DataAwareRule
{
    protected $data;
    protected $storedValue;
    protected $max;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($max, $storedValue)
    {
        $this->max = $max;
        $this->storedValue = $storedValue;
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

        if (empty($value)) {
            return true;
        }

        $deleteMediaIds = $this->data[$index]['delete_media'] ?? [];

        $deleteMediaIds = array_intersect($this->storedValue, $deleteMediaIds);

        $countedFileNumber = count($value) + count($this->storedValue) - count($deleteMediaIds);

        return $countedFileNumber <= $this->max;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The validation error message.';
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
