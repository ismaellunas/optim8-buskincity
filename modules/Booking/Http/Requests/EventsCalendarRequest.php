<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventsCalendarRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'dates' => [
                'array',
                'min:1',
            ],
            'dates.0' => [
                'required',
                'date_format:Y-m-d',
            ],
            'dates.1' => [
                'nullable',
                'date_format:Y-m-d',
            ],
            'country' => [
                'max:2',
            ],
            'city' => [
                'max:128',
            ]
        ];
    }
}
