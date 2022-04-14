<?php

namespace App\Http\Requests;

use App\Rules\HexadecimalColor;
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
        $stripeSettingService = app(StripeSettingService::class);

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
            'is_enabled' => [
                'boolean'
            ],
            'logo.file' => [
                'nullable',
                'file',
                'max:'.$stripeSettingService->maxLogoSize(),
                'mimes:'.implode(',', $stripeSettingService->logoMimeTypes()),
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

        $displayNames = (new StripeSettingService())->displayNames();

        foreach ($displayNames as $key => $displayName) {
            $key = str_replace("stripe_", "", $key);

            $attributes[$key] = $displayName;
        }

        return $attributes;
    }
}
