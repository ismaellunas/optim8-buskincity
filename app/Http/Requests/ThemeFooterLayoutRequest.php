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
            'social_media_menus.*.icon' => [
                'sometimes',
                'required',
                'min:1',
                'max:100',
            ],
            'social_media_menus.*.url' => [
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
            'icon',
            'url',
        ];

        foreach ($columns as $column) {
            for ($i = 0; $i < count($this['social_media_menus']); $i++) {
                $attr["social_media_menus.".$i.".".$column] = ucwords(str_replace('_', ' ', $column))." on social media";
            }
        }

        return $attr;
    }
}
