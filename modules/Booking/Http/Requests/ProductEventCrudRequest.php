<?php

namespace Modules\Booking\Http\Requests;

use App\Enums\PublishingStatus;
use App\Helpers\StringManipulator;
use App\Http\Requests\BaseFormRequest;
use App\Rules\CountryCode;
use App\Rules\MaxWords;
use App\Rules\Timezone;
use Astrotomic\Translatable\Validation\RuleFactory;
use Illuminate\Validation\Rule;
use Modules\Booking\Entities\ProductEventTranslation;
use Modules\Booking\Rules\MaxInclusiveDaySpan;
use Modules\Booking\Services\ProductEventService;
use Modules\Ecommerce\Entities\Product;

class ProductEventCrudRequest extends BaseFormRequest
{
    protected $errorBag = 'productEventValidation';

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $translationRules = RuleFactory::make([
            'translations.%excerpt%' => [
                'sometimes',
                new MaxWords(config('constants.max_words.excerpt')),
            ],
            'translations.%description%' => [
                'sometimes',
                'max:65000',
            ],
        ]);

        /** @var Product|null $product */
        $product = $this->route('product');
        $requiresFourteenDayCap = app(ProductEventService::class)
            ->requiresFourteenDayBookableWindow($product);

        $endedAtRules = [
            'required',
            'date',
            'after_or_equal:started_at',
        ];

        if ($requiresFourteenDayCap) {
            $endedAtRules[] = new MaxInclusiveDaySpan('started_at', 14);
        }

        return array_merge($translationRules, [
            'title' => [
                'required',
                'max:255',
            ],
            'started_at' => [
                'required',
                'date',
            ],
            'ended_at' => $endedAtRules,
            'timezone' => ['nullable', new Timezone()],
            'address' => ['nullable', 'max:500'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'city' => [
                'nullable',
                'max:64',
            ],
            'country_code' => [
                'nullable',
                new CountryCode()
            ],
            'status' => [
                'required',
                Rule::in(PublishingStatus::options()->pluck('id')),
            ],
            'weekly_hours' => ['nullable', 'array'],
            'weekly_hours.*.day' => ['integer'],
            'weekly_hours.*.hours' => ['array'],
            'weekly_hours.*.hours.*.started_time' => ['date_format:H:i'],
            'weekly_hours.*.hours.*.ended_time' => [
                'date_format:H:i',
                'after:weekly_hours.*.hours.*.started_time',
            ],
            'weekly_hours.*.is_available' => ['boolean'],
            'date_overrides' => ['nullable', 'array'],
        ]);
    }

    protected function customAttributes(): array
    {
        $translatedAttributes = [
            'timezone' => __('Timezone'),
            'title' => __('Title'),
            'started_at' => __('Started at'),
            'ended_at' => __('Ended at'),
            'city' => __('City'),
        ];

        $locales = array_keys($this->translations);

        $attributes = (new ProductEventTranslation())->getFillable();

        foreach ($locales as $locale) {
            foreach ($attributes as $attribute) {
                $attributeKey = 'translations.'.$locale.'.'.$attribute;
                $translatedAttributes[$attributeKey] = StringManipulator::snakeToTitle($attribute);
            }
        }

        return $translatedAttributes;
    }
}
