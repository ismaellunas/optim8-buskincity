<?php

namespace App\Http\Requests;

use App\Services\StripeService;
use App\Services\StripeSettingService;
use Illuminate\Validation\Rule;

class DonationRequest extends BaseFormRequest
{
    protected $errorBag = 'donation';

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $stripeService = app(StripeService::class);

        $minimalPayment = $this->getMinimalPayment();

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

    private function getMinimalPayment()
    {
        $currency = $this->currency;
        $stripeService = app(StripeService::class);

        $minimalPayment = $stripeService->getMinimalPaymentWithFee(
            $stripeService->getCurrencyMinimalPayment($currency)
        );

        $currencyMinimalPayment = app(StripeSettingService::class)->getMinimalAmountByCurrency($currency);

        return $currencyMinimalPayment > $minimalPayment
            ? $currencyMinimalPayment
            : $minimalPayment;
    }
}
