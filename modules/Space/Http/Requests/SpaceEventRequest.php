<?php

namespace Modules\Space\Http\Requests;

use App\Helpers\StringManipulator;
use App\Http\Requests\BaseFormRequest;
use App\Rules\CountryCode;
use App\Rules\MaxWords;
use App\Rules\Timezone;
use Astrotomic\Translatable\Validation\RuleFactory;
use Modules\Space\Entities\SpaceEventTranslation;

class SpaceEventRequest extends BaseFormRequest
{
    protected $errorBag = 'eventValidation';

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

        return array_merge($translationRules, [
            'title' => [
                'required',
                'max:255',
            ],
            'started_at' => [
                'required',
                'date',
            ],
            'ended_at' => [
                'required',
                'date',
            ],
            'timezone' => ['required', new Timezone()],
            'address' => ['nullable', 'max:500'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'city' => ['required', 'max:64'],
            'country_code' => ['required', new CountryCode()],
        ]);
    }

    protected function customAttributes(): array
    {
        $translatedAttributes = [
            'timezone' => __('Timezone'),
            'title' => __('Title'),
            'started_at' => __('Started at'),
            'ended_at' => __('Ended at'),
        ];

        $locales = array_keys($this->translations);

        $attributes = (new SpaceEventTranslation())->getFillable();

        foreach ($locales as $locale) {
            foreach ($attributes as $attribute) {
                $attributeKey = 'translations.'.$locale.'.'.$attribute;
                $translatedAttributes[$attributeKey] = StringManipulator::snakeToTitle($attribute);
            }
        }

        return $translatedAttributes;
    }
}
