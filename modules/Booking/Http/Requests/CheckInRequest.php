<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Booking\Rules\CheckInPosition;

class CheckInRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->can('checkIn', $this->route('order'));
    }

    public function rules()
    {
        return [
            'geolocation.latitude' => [
                'required',
                'numeric'
            ],
            'geolocation.longitude' => [
                'required',
                'numeric'
            ],
            'geolocation' => [
                'array',
                new CheckInPosition($this->route('order'), 'geolocation.latitude', 'geolocation.longitude'),
            ],
        ];
    }
}
