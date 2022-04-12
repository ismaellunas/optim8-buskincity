<?php

namespace App\Http\Requests;

use App\Rules\HexadecimalColor;
use App\Services\SettingService;

class ThemeColorRequest extends BaseFormRequest
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
            '*' => new HexadecimalColor(),
        ];
    }

    protected function customAttributes(): array
    {
        $attributes = [];

        $colors = (new SettingService())->getColors();

        foreach ($colors as $key => $color) {
            $attributes[$key] = $color['display_name'];
        }

        return $attributes;
    }
}
