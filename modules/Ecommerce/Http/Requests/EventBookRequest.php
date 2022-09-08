<?php

namespace Modules\Ecommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Ecommerce\Enums\ProductStatus;
use Modules\Ecommerce\Rules\AvailableBookingTime;

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

        return [
            'date' => [
                'required',
                'date_format:Y-m-d',
            ],
            'time' => [
                'required',
                'date_format:H',
                new AvailableBookingTime($product->eventSchedule),
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
