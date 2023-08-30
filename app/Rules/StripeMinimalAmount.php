<?php

namespace App\Rules;

use App\Services\StripeService;
use Illuminate\Contracts\Validation\Rule;

class StripeMinimalAmount implements Rule
{
    private $selectedCurrency = null;
    private $selectedAmount = null;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $listMinimalPayments = app(StripeService::class)->getListMinimalPayments();

        foreach ($value as $currency => $minimalAmount) {
            if ($listMinimalPayments[$currency] > $minimalAmount) {
                $this->selectedCurrency = $currency;
                $this->selectedAmount = $listMinimalPayments[$currency];

                return false;
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
                'attribute' => __('Minimal payment') . ' ' . $this->selectedCurrency,
                'value' => $this->selectedAmount,
            ]
        );
    }
}
