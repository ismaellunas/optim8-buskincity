<?php

namespace App\Http\Requests;

use App\Helpers\StringManipulator;
use App\Models\CategoryTranslation;
use Astrotomic\Translatable\Validation\RuleFactory;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryRequest extends BaseFormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $inputs = $this->findInputByLocale();

        return RuleFactory::make([
            '%name%' => ['sometimes', 'required'],
            '%slug%' => [
                'sometimes',
                'required',
                'alpha_dash',
                Rule::unique('category_translations', 'slug')
                    ->where(function ($query) use ($inputs) {
                        return $query->where('slug', $inputs['slug'])
                            ->where('locale', $inputs['locale']);
                    })
                    ->ignore($inputs['id'] ?? null)
            ],
            '%meta_description%' => [
                'sometimes',
                'max:'.config('constants.max_length.meta_description'),
            ],
            '%meta_title%' => [
                'sometimes',
                'max:'.config('constants.max_length.meta_title'),
            ],
        ]);
    }

    protected function customAttributes(): array
    {
        $translatedAttributes = [];
        $locales = array_keys($this->all());

        $attributes = (new CategoryTranslation())->getFillable();

        foreach ($locales as $locale) {
            foreach ($attributes as $attribute) {
                $attributeKey = $locale.'.'.$attribute;
                $translatedAttributes[$attributeKey] = StringManipulator::snakeToTitle($attribute);
            }
        }

        return $translatedAttributes;
    }

    private function findInputByLocale(): array
    {
        $inputs = [];
        $locales = array_keys($this->all());

        foreach ($locales as $locale) {
            if (count($this[$locale]) > 0) {
                $inputs = $this[$locale];
                $inputs['locale'] = $locale;
            }
        }

        return $inputs;
    }

    protected function prepareForValidation()
    {
        $locales = array_keys($this->all());

        foreach ($locales as $locale) {
            if (count($this[$locale]) > 0) {
                $input = $this[$locale];

                $this->merge([
                    $locale => array_merge(
                        $input,
                        [
                            'slug' => Str::of($input['slug'])
                                ->ascii()
                                ->slug('-')
                                ->__toString(),
                        ]
                    ),
                ]);
            }
        }
    }
}
