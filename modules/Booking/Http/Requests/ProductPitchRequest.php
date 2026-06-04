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
use Modules\Booking\Services\ProductSpaceService;
use Modules\Ecommerce\Enums\ProductStatus;
use Modules\Ecommerce\ModuleService as EcommerceModuleService;
use Modules\Ecommerce\Services\ProductService;

class ProductPitchRequest extends BaseFormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('timezone')) {
            $this->merge(['pitch_timezone' => $this->input('timezone')]);
        }
    }

    public function rules(): array
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

        $rules = [
            // Product
            'name' => ['required', 'max:200'],
            'description' => ['nullable', 'max:1000'],
            'short_description' => ['nullable', 'max:500'],
            'status' => ['required', Rule::in(array_column(ProductStatus::cases(), 'value'))],
            'roles' => ['nullable', Rule::in(app(ProductService::class)->roleOptions()->pluck('id')->filter())],
            'is_check_in_required' => ['required', 'boolean'],
            'gallery' => ['nullable', 'array', 'max:' . EcommerceModuleService::maxProductMediaNumber()],
            'gallery.*' => ['exists:media,id'],

            // Pitch dates — required per T4.1 decision
            'pitch_started_at' => ['required', 'date'],
            'pitch_ended_at' => $pitchEndedAtRules,
            'pitch_timezone' => ['nullable', new Timezone()],
            'bookable_date_range' => [
                'required',
                'integer',
                'min:0',
                'max:'.($requiresFourteenDayCap ? 14 : 365),
            ],
            'duration' => ['required', Rule::in(
                app(ProductEventService::class)->durationOptions()->pluck('id')
            )],

            // Schedule
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

            // Date overrides
            'date_overrides' => ['array'],
            'date_overrides.*.ended_date' => ['nullable', 'date_format:Y-m-d'],
            'date_overrides.*.started_date' => ['date_format:Y-m-d'],
            'date_overrides.*.times' => ['array'],
            'date_overrides.*.times.*.started_time' => ['date_format:H:i'],
            'date_overrides.*.times.*.ended_time' => [
                'date_format:H:i',
                'after:date_overrides.*.times.*.started_time',
            ],
            'date_overrides.*.times.*' => [new NoOverlappingTime()],

            // Location
            'location.address' => ['nullable', 'max:500'],
            'location.latitude' => ['nullable', 'numeric'],
            'location.longitude' => ['nullable', 'numeric'],
            'location.city' => ['required', 'max:64'],
            'location.country_code' => ['required', new CountryCode()],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id', new InScopedCityId()],
        ];

        if (\Nwidart\Modules\Facades\Module::has('Space') && \Nwidart\Modules\Facades\Module::isEnabled('Space')) {
            $currentSpaceId = $this->route('product')?->productable_id;

            $spaceIds = app(ProductSpaceService::class)
                ->getSpaceOptions($currentSpaceId)
                ->filter(fn ($space) => ! $space['is_disabled'])
                ->pluck('id');

            $rules['space_id'] = ['nullable', Rule::in($spaceIds)];
        }

        return $rules;
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function customAttributes(): array
    {
        return [
            'pitch_started_at' => 'Pitch Start',
            'pitch_ended_at' => 'Pitch End',
            'pitch_timezone' => 'Pitch Timezone',
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
