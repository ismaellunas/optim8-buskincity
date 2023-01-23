<?php

namespace Modules\FormBuilder\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\FormBuilder\Rules\FieldNameRequired;

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
            'form_id' => [
                'required',
                'max: 127',
                'regex:/^[a-z0-9\_]+$/',
                'unique:forms,key' . (
                    $this->id ? ',' . $this->id : null
                ),
            ],
            'setting' => [
                'nullable',
            ],
            'field_groups.*.title' => [
                'nullable',
            ],
            'field_groups.*.fields' => [
                'nullable',
                new FieldNameRequired(),
            ],
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
