<?php

namespace App\Http\Requests;

use App\Services\SettingService;

class MediaUpdateImageRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update', $this->route('medium'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $max = SettingService::maxFileSize();

        return [
            'image' => [
                'required',
                'file',
                'max:'.$max,
                'mimes:'.implode(',', config('constants.extensions.image')),
            ],
        ];
    }
}
