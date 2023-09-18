<?php

namespace Modules\Booking\Http\Requests;

use App\Http\Requests\BaseFormRequest;
use App\Rules\CountryCode;
use App\Rules\Timezone;
use Illuminate\Validation\Rule;
use Modules\Booking\Rules\NoOverlappingTime;
use Modules\Booking\Services\ProductEventService;

class ProductEventRequest extends BaseFormRequest
{
    protected $errorBag = 'updateEvent';

    public function rules()
    {
        return [
            'bookable_date_range' => ['required', 'integer', 'min:0', 'max:365'],
            'date_overrides' => ['array'],
            'date_overrides.*.ended_date' => ['nullable', 'date_format:Y-m-d'],
            'date_overrides.*.started_date' => ['date_format:Y-m-d'],
            'date_overrides.*.times' => ['array'],
            'date_overrides.*.times.*.started_time' => ['date_format:H:i'],
            'date_overrides.*.times.*.ended_time' => [
                'date_format:H:i',
                'after:date_overrides.*.times.*.started_time'
            ],
            'date_overrides.*.times.*' => [new NoOverlappingTime()],
            'duration' => [
                'required',
                Rule::in(
                    app(ProductEventService::class)
                        ->durationOptions()
                        ->pluck('id')
                )
            ],
            'timezone' => ['required', new Timezone()],
            'weekly_hours' => ['array'],
            'weekly_hours.*.day' => ['integer'],
            'weekly_hours.*.hours' => ['array'],
            'weekly_hours.*.hours.*.started_time' => ['date_format:H:i'],
            'weekly_hours.*.hours.*.ended_time' => [
                'date_format:H:i',
                'after:weekly_hours.*.hours.*.started_time',
            ],
            'weekly_hours.*.is_available' => ['boolean'],
            'weekly_hours.*.hours.*' => [new NoOverlappingTime()],
            'location.address' => ['nullable', 'max:500'],
            'location.latitude' => ['nullable', 'numeric'],
            'location.longitude' => ['nullable', 'numeric'],
            'location.city' => ['required', 'max:64'],
            'location.country_code' => ['required', new CountryCode()],
        ];
    }

    public function authorize()
    {
        return true;
    }

    protected function customAttributes(): array
    {
        return [
            'weekly_hours.*.hours.*.started_time' => 'Start Time',
            'weekly_hours.*.hours.*.ended_time' => 'End Time',
            'date_overrides.*.times.*.started_time' => 'Start Time',
            'date_overrides.*.times.*.ended_time' => 'End Time',
            'date_overrides.*.started_date' => 'Start Date',
            'location.latitude' => 'Latitude',
            'location.longitude' => 'Longitude',
            'location.city' => 'City',
            'location.country_code' => 'Country',
        ];
    }
}
