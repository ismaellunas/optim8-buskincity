<?php

namespace Modules\Ecommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Ecommerce\Services\ProductEventService;

class ProductEventRequest extends FormRequest
{
    public function rules()
    {
        return [
            'bookable_date_range' => ['required', 'numeric'],
            'date_overrides' => ['array'],
            'date_overrides.*.ended_date' => ['nullable', 'date_format:Y-m-d'],
            'date_overrides.*.started_date' => ['date_format:Y-m-d'],
            'date_overrides.*.is_available' => ['boolean'],
            'date_overrides.*.times' => ['array'],
            'date_overrides.*.times.*.ended_time' => ['date_format:H:i'],
            'date_overrides.*.times.*.started_time' => ['date_format:H:i'],
            'duration' => ['required', 'numeric'],
            'timezone' => [
                'required',
                Rule::in(
                    app(ProductEventService::class)
                        ->timezoneOptions()
                        ->pluck('id')
                )
            ],
            'weekly_hours' => ['array'],
            'weekly_hours.*.day' => ['integer'],
            'weekly_hours.*.hours' => ['array'],
            'weekly_hours.*.hours.*.ended_time' => ['date_format:H:i'],
            'weekly_hours.*.hours.*.started_time' => ['date_format:H:i'],
            'weekly_hours.*.is_available' => ['boolean'],
        ];
    }

    public function authorize()
    {
        return true;
    }
}
