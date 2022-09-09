<?php

namespace Modules\Ecommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRescheduleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'date' => [
                'required',
                'date_format:Y-m-d',
            ],
            'time' => [
                'required',
                'date_format:H:i',
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
        return $this->user()->can('reschedule', $this->route('order'));
    }
}
