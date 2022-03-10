<?php

namespace App\Http\Requests;

use App\Services\StripeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DonationRequest extends FormRequest
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

        return [
            'amount' => [
                'required',
                'integer',
                'gte:'.$minimalPayment,
            ],
            'currency' => [
                'required',
                Rule::in(['SEK', 'EUR', 'USD']),
            ],
        ];
    }
}
