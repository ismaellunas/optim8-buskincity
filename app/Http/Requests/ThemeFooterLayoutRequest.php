<?php

namespace App\Http\Requests;

class ThemeFooterLayoutRequest extends BaseFormRequest
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
                'max:100',
            ],
            'social_media_menus.*.url' => [
                'sometimes',
                'required',
                'url',
            ],
        ];
    }

    protected function customAttributes(): array
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
