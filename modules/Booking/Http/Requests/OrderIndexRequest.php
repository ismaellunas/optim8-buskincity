<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Booking\Enums\BookingStatus;

class OrderIndexRequest extends FormRequest
{
    public function rules()
    {
        return [
            'dates' => [
                'nullable',
                'array'
            ],
            'dates.*' => [
                'nullable',
                'date_format:Y-m-d'
            ],
            'status' => [
                'nullable',
                Rule::in(BookingStatus::options()->pluck('id'))
            ],
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
            ],
            'city' => [
                'nullable',
                'max:100',
            ],
        ];
    }

    public function authorize()
    {
        return true;
    }
}
