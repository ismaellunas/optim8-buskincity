<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class StripeMinimalAmountOption implements Rule
{
    private $selectedCurrency = null;
    private $selectedAmount = null;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(private array $minimalAmounts)
    {
        //
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
        foreach ($value as $currency => $amountOptions) {
            foreach ($amountOptions as $amount) {
                if ($this->minimalAmounts[$currency] > $amount) {
                    $this->selectedCurrency = $currency;
                    $this->selectedAmount = $this->minimalAmounts[$currency];

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __(
            'The :attribute must be greater than or equal :value.',
            [
                'attribute' => __('Amount options') . ' ' . $this->selectedCurrency,
                'value' => $this->selectedAmount,
            ]
        );
    }
}
