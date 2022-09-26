<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingKeyRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [];
        $fields = array_keys($this->all());

        foreach ($fields as $field) {
            $rules[$field] = [
                'nullable',
                'max:255',
            ];
        }

        return $rules;
    }
}
