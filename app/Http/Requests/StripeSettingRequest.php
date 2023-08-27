<?php

namespace App\Http\Requests;

use App\Rules\HexadecimalColor;
use App\Rules\StripeMinimalAmount;
use App\Rules\StripeMinimalAmountOption;
use App\Services\StripeService;
use App\Services\StripeSettingService;
use Illuminate\Validation\Rule;

class StripeSettingRequest extends BaseFormRequest
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
                'array',
                new StripeMinimalAmountOption($this->minimal_amounts),
            ],
            'amount_options.*' => [
                'array',
            ],
            'amount_options.*.*' => [
                'numeric',
            ],
            'application_fee_percentage' => [
                'numeric',
                'min:0',
            ],
            'default_country' => [
                'required',
                Rule::in($countries),
            ],
            'is_enabled' => [
                'boolean'
            ],
            'logo' => [
                'nullable',
                'exists:media,id',
            ],
            'minimal_amounts' => [
                'array',
                new StripeMinimalAmount
            ],
            'minimal_amounts.*' => [
                'nullable',
                'numeric',
            ],
            'payment_currencies' => [
                'array'
            ],
            'payment_currencies.*' => [
                Rule::in($currencies)
            ],
            'color_primary' => [
                new HexadecimalColor(),
            ],
            'color_secondary' => [
                new HexadecimalColor(),
            ],
        ];
    }

    protected function customAttributes(): array
    {
        $attributes = [];

        $displayNames = app(StripeSettingService::class)->displayNames();

        foreach ($displayNames as $key => $displayName) {
            $key = str_replace("stripe_", "", $key);

            $attributes[$key] = $displayName;
        }

        return $attributes;
    }
}
