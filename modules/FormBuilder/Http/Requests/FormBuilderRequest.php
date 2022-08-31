<?php

namespace Modules\FormBuilder\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FormBuilderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'max: 127',
            ],
            'key' => [
                'required',
                'max: 127',
                'regex:/^[a-z0-9\_]+$/',
                'unique:field_groups,title' . (
                    $this->id ? ',' . $this->id : null
                ),
            ],
            'builders' => [
                'nullable',
            ]
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
