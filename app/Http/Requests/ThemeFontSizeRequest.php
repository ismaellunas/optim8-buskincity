<?php

namespace App\Http\Requests;

use App\Services\SettingService;

class ThemeFontSizeRequest extends BaseFormRequest
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
            '*' => [
                'numeric',
                'min:0',
                'regex:/^\d+\.?\d*$/',
            ],
        ];
    }

    protected function customAttributes(): array
    {
        $attributes = [];

        $fontSizes = (new SettingService())->getFontSizes();

        foreach ($fontSizes as $key => $fontSize) {
            $attributes[$key] = $fontSize['display_name'];
        }

        return $attributes;
    }
}
