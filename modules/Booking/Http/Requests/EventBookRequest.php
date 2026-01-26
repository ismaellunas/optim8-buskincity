<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Booking\Rules\AvailableBookingDate;
use Modules\Booking\Rules\AvailableBookingTime;
use Modules\Booking\Entities\ProductEvent;
use Modules\Ecommerce\Enums\ProductStatus;

class EventBookRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $product = $this->route('product');
        $schedule = $product->eventSchedule;

        $productEventId = $this->get('product_event_id');
        if ($productEventId) {
            $productEvent = ProductEvent::where('product_id', $product->id)
                ->find($productEventId);

            if ($productEvent && $productEvent->schedule) {
                $schedule = $productEvent->schedule;
            }
        }

        return [
            'product_event_id' => ['nullable', 'integer', 'exists:product_events,id'],
            'date' => [
                'required',
                'date_format:Y-m-d',
                new AvailableBookingDate($schedule),
            ],
            'time' => [
                'required',
                'date_format:H:i',
                new AvailableBookingTime($schedule),
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
        $product = $this->route('product');

        return (
            $product->eventSchedule
            && $product->status == ProductStatus::PUBLISHED->value
        );
    }
}
