<?php

namespace Modules\Space\Http\Requests;

use App\Http\Requests\BaseFormRequest;

class ContactRequest extends BaseFormRequest
{
    protected $errorBag = 'contactValidation';

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => [
                'required',
                'max:128',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'phone.number' => [
                'nullable',
                'phone:phone.country',
            ],
            'phone.country' => [
                'required_with:phone.number',
            ],
        ];
    }

    protected function customAttributes(): array
    {
        return [
            'phone.number' => __('Number'),
            'phone.country' => __('Country'),
        ];
    }
}
