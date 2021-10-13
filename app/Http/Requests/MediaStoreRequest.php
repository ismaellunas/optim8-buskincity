<?php

namespace App\Http\Requests;

use App\Rules\AlphaNumericDash;
use App\Services\MediaService;
use App\Services\TranslationService;
use Illuminate\Foundation\Http\FormRequest;

class MediaStoreRequest extends FormRequest
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

        return array_merge(
            MediaService::getTranslationRules(),
            [
                'file_name' => [
                    'required',
                    new AlphaNumericDash(),
                    'max:250',
                ],
                'file' => [
                    'required',
                    'file',
                    'max:'.config('constants.one_megabyte') * 50,
                    'mimes:'.implode(',', $mimes),
                ],
            ]
        );
    }

    public function attributes(): array
    {
        $attrs = TranslationService::getCustomAttributes(
            array_keys($this->input('translations', [])),
            ['alt', 'description']
        );
        return $attrs;
    }
}
