<?php

namespace App\Http\Requests;

use App\Rules\HexadecimalColor;
use App\Services\SettingService;

class ThemeColorRequest extends BaseFormRequest
{
    private $colorSettings;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    private function getColorSettings(): array
    {
        if (is_null($this->colorSettings)) {
            $this->colorSettings = app(SettingService::class)->getColors();
        }

        return $this->colorSettings;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];

        $colorKeys = array_keys($this->getColorSettings());

        foreach ($colorKeys as $key) {
            $rules[$key] = new HexadecimalColor();
        }

        return $rules;
    }

    protected function customAttributes(): array
    {
        $attributes = [];

        foreach ($this->getColorSettings() as $key => $color) {
            $attributes[$key] = $color['display_name'];
        }

        return $attributes;
    }
}
