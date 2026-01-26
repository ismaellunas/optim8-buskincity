<?php

namespace Modules\Booking\Http\Requests;

use App\Http\Requests\BaseFormRequest;
use App\Rules\Timezone;
use Modules\Booking\Rules\NoOverlappingTime;

class ProductEventScheduleRequest extends BaseFormRequest
{
    protected $errorBag = 'productEventSchedule';

    public function rules()
    {
        return [
            'timezone' => ['required', new Timezone()],
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
        ];
    }
}
