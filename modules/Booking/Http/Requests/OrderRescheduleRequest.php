<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Booking\Rules\AvailableBookingDate;
use Modules\Booking\Rules\AvailableBookingTime;

class OrderRescheduleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $product = ($this->route('order'))->firstProduct;

        return [
            'date' => [
                'required',
                'date_format:Y-m-d',
                new AvailableBookingDate($product->eventSchedule),
            ],
            'time' => [
                'required',
                'date_format:H:i',
                new AvailableBookingTime($product->eventSchedule),
            ],
            'message' => [
                'nullable',
                'max:500',
            ],
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
