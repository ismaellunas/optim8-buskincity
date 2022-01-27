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

        $storedValue = $this->storedValue ?? [];

        $deleteMediaIds = $this->data[$index]['delete_media'] ?? [];

        $deleteMediaIds = array_intersect($storedValue, $deleteMediaIds);

        $countedFileNumber = count($value) + count($storedValue) - count($deleteMediaIds);

        return $countedFileNumber <= $this->max;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must not have more than '.$this->max.' file(s).';
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
