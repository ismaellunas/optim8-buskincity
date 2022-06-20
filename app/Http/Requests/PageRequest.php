<?php

namespace App\Http\Requests;

use App\Helpers\StringManipulator;
use App\Models\PageTranslation;
use Astrotomic\Translatable\Validation\RuleFactory;
use Illuminate\Validation\Rule;

class PageRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $inputs = $this->findInputByLocale();

        return RuleFactory::make([
            '%title%' => 'sometimes|string',
            '%slug%' => [
                'sometimes',
                'required',
                'alpha_dash',
                Rule::unique('page_translations', 'slug')
                    ->where(function ($query) use ($inputs) {
                        return $query->where('slug', $inputs['slug'])
                            ->where('locale', $inputs['locale']);
                    })
                    ->ignore($inputs['id'])
            ],
            '%meta_title%' => [
                'sometimes',
                'max:'.config('constants.max_length.meta_title'),
            ],
            '%meta_description%' => [
                'sometimes',
                'max:'.config('constants.max_length.meta_description'),
            ],
        ]);
    }

    protected function customAttributes(): array
    {
        $translatedAttributes = [];
        $locales = array_keys($this->all());

        $attributes = (new PageTranslation())->getFillable();

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
}
