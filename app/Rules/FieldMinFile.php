<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class FieldMinFile implements Rule, DataAwareRule
{
    protected $data;
    protected $min;
    protected $storedValue;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($min, $storedValue)
    {
        $this->min = $min;
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

        $deleteMediaIds = $this->data[$index]['delete_media'] ?? [];

        $existingMediaIds = array_diff($this->storedValue, $deleteMediaIds);

        $countedFileNumber = count($value) + count($existingMediaIds);

        return $countedFileNumber >= $this->min;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute must have at least '.$this->min.' file(s).';
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
