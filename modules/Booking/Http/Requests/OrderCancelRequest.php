<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderCancelRequest extends FormRequest
{
    public function rules()
    {
        return [
            'message' => [
                'nullable',
                'max:500',
            ],
        ];
    }

    public function authorize()
    {
        return true;
    }
}
