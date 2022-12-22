<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class MediaIndexRequest extends FormRequest
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
        $types = array_keys(config('constants.extensions'));

        return [
            'term' => [
                'nullable',
                'max: 1024',
            ],
            'view' => [
                'nullable',
            ],
            'types' => [
                'sometimes',
                'array'
            ],
            'types.*' => [
                'string',
                Rule::in($types),
            ],
        ];
    }
}
