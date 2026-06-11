<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Booking\Rules\AvailableBookingDate;
use Modules\Booking\Rules\AvailableBookingTime;
use Modules\Booking\Rules\BookingWithinPitchWindow;
use Modules\Ecommerce\Enums\ProductStatus;

class EventBookRequest extends FormRequest
{
    public function rules()
    {
        $product = $this->route('product');
        $schedule = $product->eventSchedule;

        return [
            'date' => [
                'required',
                'date_format:Y-m-d',
                new AvailableBookingDate($schedule),
                new BookingWithinPitchWindow($product),
            ],
            'time' => [
                'required',
                'date_format:H:i',
                new AvailableBookingTime($schedule),
            ],
        ];
    }

    public function authorize()
    {
        $product = $this->route('product');

        return (
            $product->eventSchedule
            && $product->status == ProductStatus::PUBLISHED->value
        );
    }
}
