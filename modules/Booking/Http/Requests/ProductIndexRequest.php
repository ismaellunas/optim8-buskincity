<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ProductIndexRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'term' => [
                'nullable',
                'max:1024',
            ],
            'column' => [
                'nullable',
                'max:128'
            ],
            'order' => [
                'nullable',
                Rule::in(['asc', 'desc'])
            ]
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
