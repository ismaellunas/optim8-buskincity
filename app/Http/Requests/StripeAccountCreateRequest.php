<?php

namespace App\Http\Requests;

use App\Services\StripeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StripeAccountCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $countries = app(StripeService::class)
            ->getCountryOptions()
            ->pluck('id')
            ->all();

        return [
            'country' => [
                'required',
                Rule::in($countries),
            ],
        ];
    }
}
