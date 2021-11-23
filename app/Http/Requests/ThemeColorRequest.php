<?php

namespace App\Http\Requests;

use App\Services\SettingService;
use Illuminate\Foundation\Http\FormRequest;

class ThemeColorRequest extends FormRequest
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
            '*' => 'regex:/^\#[\da-f]{6}$/i',
        ];
    }

    public function attributes()
    {
        $attributes = [];

        $colors = (new SettingService())->getColors();

        foreach ($colors as $key => $color) {
            $attributes[$key] = $color['display_name'];
        }

        return $attributes;
    }
}
