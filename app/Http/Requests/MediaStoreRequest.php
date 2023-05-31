<?php

namespace App\Http\Requests;

use App\Rules\AlphaNumericDash;
use App\Services\MediaService;
use App\Services\SettingService;
use App\Services\TranslationService;
use Illuminate\Support\Str;

class MediaStoreRequest extends BaseFormRequest
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
        $mimes = MediaService::getExtensions();
        $max = SettingService::maxFileSize();

        return [
            ...[
                '*.file_name' => [
                    'required',
                    new AlphaNumericDash(),
                    'max:250',
                ],
                '*.file' => [
                    'sometimes',
                    'file',
                    'max:'.$max,
                    'mimes:'.implode(',', $mimes),
                ],
            ],
            ...collect(MediaService::getTranslationRules())
                ->mapWithKeys(function ($rule, $key) {
                    return [
                        '*.'.$key => $rule
                    ];
                })
                ->all()
        ];
    }

    protected function customAttributes(): array
    {
        $attrs = [];

        foreach ($this->all() as $index => $inputs) {
            $translationAttrs = TranslationService::getCustomAttributes(
                array_keys($inputs['translations']),
                ['alt', 'description']
            );

            $inputs = collect($inputs)->except('translations')->all();

            foreach (array_keys($inputs) as $inputKey) {
                $attrIndex = $index . '.' . $inputKey;

                $attrs[$attrIndex] = Str::of($inputKey)
                    ->replace('_', ' ')
                    ->value();
            }

            $attrs = [
                ...$attrs,
                ...collect($translationAttrs)
                    ->mapWithKeys(function ($attr, $key) use ($index) {
                        return [
                            $index.'.'.$key => $attr
                        ];
                    })
                    ->all()
            ];
        }

        return $attrs;
    }
}
