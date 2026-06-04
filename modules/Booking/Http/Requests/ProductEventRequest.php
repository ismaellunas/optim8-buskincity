<?php

namespace Modules\Booking\Http\Requests;

use App\Http\Requests\BaseFormRequest;
use App\Rules\CountryCode;
use App\Rules\InScopedCityId;
use App\Rules\Timezone;
use Illuminate\Validation\Rule;
use Modules\Booking\Rules\MaxInclusiveDaySpan;
use Modules\Booking\Rules\NoOverlappingTime;
use Modules\Booking\Services\ProductEventService;
use Modules\Ecommerce\Entities\Product;

class ProductEventRequest extends BaseFormRequest
{
    protected $errorBag = 'updateEvent';

    protected function prepareForValidation(): void
    {
        if ($this->has('timezone')) {
            $this->merge(['pitch_timezone' => $this->input('timezone')]);
        }
    }

    public function rules()
    {
        /** @var ProductEventService $eventService */
        $eventService = app(ProductEventService::class);

        /** @var Product|null $product */
        $product = $this->route('product');
        $requiresFourteenDayCap = $eventService->requiresFourteenDayBookableWindow($product);

        $pitchEndedAtRules = ['required', 'date', 'after_or_equal:pitch_started_at'];
        if ($requiresFourteenDayCap) {
            $pitchEndedAtRules[] = new MaxInclusiveDaySpan('pitch_started_at', 14);
        }

        return [
            'pitch_started_at' => ['required', 'date'],
            'pitch_ended_at' => $pitchEndedAtRules,
            'pitch_timezone' => ['nullable', new Timezone()],
            'bookable_date_range' => [
                'required',
                'integer',
                'min:0',
                'max:'.($requiresFourteenDayCap ? 14 : 365),
            ],
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
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id', new InScopedCityId()],
        ];
    }

    public function authorize()
    {
        return true;
    }

    protected function customAttributes(): array
    {
        return [
            'pitch_started_at' => 'Pitch Start',
            'pitch_ended_at' => 'Pitch End',
            'pitch_timezone' => 'Timezone',
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
