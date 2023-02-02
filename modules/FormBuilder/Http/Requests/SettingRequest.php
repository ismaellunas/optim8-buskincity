<?php

namespace Modules\FormBuilder\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class SettingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'button.text' => [
                'nullable',
                'max:127'
            ],
            'button.position' => [
                'nullable',
                'max:64'
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

    public function attributes(): array
    {
        $keys = array_keys($this->rules());

        $attributes = [];

        foreach ($keys as $key) {
            $attributes[$key] = Str::of($key)
                ->replace('.', ' ')
                ->replace('_', ' ')
                ->title()
                ->value();
        }

        return $attributes;
    }
}
