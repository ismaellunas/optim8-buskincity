<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ThemeFooterLayoutRequest extends FormRequest
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
            'links.*.file' => [
                'sometimes',
                'nullable',
                'file',
                'max:'.config('constants.one_megabyte') * 50,
                'mimes:jpeg,jpg,png',
            ],
            'links.*.url' => [
                'sometimes',
                'required',
                'url',
            ],
        ];
    }

    public function attributes()
    {
        $attr = [];
        $columns = [
            'file',
            'url',
        ];

        foreach ($columns as $column) {
            for ($i = 0; $i < count($this['links']); $i++) {
                $attr["links.".$i.".".$column] = ucwords(str_replace('_', ' ', $column));
            }
        }

        return $attr;
    }
}
