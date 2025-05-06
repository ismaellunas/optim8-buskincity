<?php

namespace App\Http\Requests;

use App\Services\StripeService;
use Illuminate\Validation\Rule;

class DonationRequest extends BaseFormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $stripeService = app(StripeService::class);

        $minimalPayment = $stripeService->getMinimalPaymentWithFee(
            $stripeService->getCurrencyMinimalPayment($this->currency)
        );

        $currencies = array_map(
            function ($option) {
                return $option['id'];
            },
            $stripeService->getCurrencyOptions()
        );

        return [
            'amount' => [
                'required',
                'integer',
                'gte:'.$minimalPayment,
            ],
            'currency' => [
                'required',
                Rule::in($currencies),
            ],
        ];
    }
}
