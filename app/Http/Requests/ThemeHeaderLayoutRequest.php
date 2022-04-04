<?php

namespace App\Http\Requests;

class ThemeHeaderLayoutRequest extends BaseFormRequest
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
        return [
            'layout' => ['required', 'integer'],
            'logo.file' => [
                'nullable',
                'file',
                'max:'.config('constants.one_megabyte') * 50,
                'mimes:'.implode(',', config('constants.extensions.image')),
            ],
        ];
    }

    protected function customAttributes(): array
    {
        return [
            'logo.file' => 'Logo',
        ];
    }
}
