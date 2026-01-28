<?php

namespace Modules\Booking\Http\Requests;

use App\Enums\PublishingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Booking\Entities\ProductEvent;
use Modules\Booking\Rules\AvailableBookingDate;
use Modules\Booking\Rules\AvailableBookingTime;
use Modules\Booking\Rules\BookingWithinProductEventRange;
use Modules\Ecommerce\Enums\ProductStatus;

class EventBookBatchRequest extends FormRequest
{
    public function rules()
    {
        $product = $this->route('product');
        $schedule = $product->eventSchedule;

        $productEventId = $this->get('product_event_id');
        $productEvent = null;
        if (!empty($productEventId)) {
            $productEvent = ProductEvent::where('product_id', $product->id)
                ->where('status', PublishingStatus::PUBLISHED->value)
                ->find($productEventId);

            if ($productEvent && $productEvent->schedule) {
                $schedule = $productEvent->schedule;
            }
        }

        $dateRules = [
            'required',
            'date_format:Y-m-d',
            new AvailableBookingDate($schedule),
        ];

        if ($productEvent) {
            $dateRules[] = new BookingWithinProductEventRange($productEvent);
        }

        return [
            'product_event_id' => [
                'required',
                'integer',
                Rule::exists('product_events', 'id')
                    ->where('product_id', $product->id)
                    ->where('status', PublishingStatus::PUBLISHED->value),
            ],
            'slots' => ['required', 'array', 'min:1'],
            'slots.*.date' => $dateRules,
            'slots.*.time' => [
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
            && $product->productEvents()
                ->where('status', PublishingStatus::PUBLISHED->value)
                ->exists()
            && $product->status == ProductStatus::PUBLISHED->value
        );
    }
}
