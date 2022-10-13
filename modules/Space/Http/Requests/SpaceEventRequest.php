<?php

namespace Modules\Space\Http\Requests;

use App\Helpers\StringManipulator;
use App\Http\Requests\BaseFormRequest;
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
                'max:150',
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
        ]);
    }

    protected function customAttributes(): array
    {
        $translatedAttributes = [];
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
