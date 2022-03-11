<?php

namespace App\Http\Requests;

use App\Services\StripeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StripeSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $stripeService = app(StripeService::class);

        $countries = $stripeService
            ->getCountryOptions()
            ->pluck('id')
            ->all();

        $currencies = collect($stripeService->getCurrencyOptions())
            ->pluck('id')
            ->all();

        return [
            'amount_options' => [
                'array'
            ],
            'amount_options.*' => [
                'array',
            ],
            'amount_options.*.*' => [
                'numeric',
                'gt:0',
            ],
            'application_fee_percentage' => [
                'numeric',
                'min:0',
            ],
            'default_country' => [
                'required',
                Rule::in($countries),
            ],
            'minimal_amounts' => [
                'array'
            ],
            'minimal_amounts.*' => [
                'nullable',
                'numeric',
                'gt:0',
            ],
            'payment_currencies' => [
                'array'
            ],
            'payment_currencies.*' => [
                Rule::in($currencies)
            ],
        ];
    }
}
