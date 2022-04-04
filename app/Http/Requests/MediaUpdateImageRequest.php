<?php

namespace App\Http\Requests;

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
        return [
            'image' => [
                'required',
                'file',
                'max:'.config('constants.one_megabyte') * 50,
                'mimes:'.implode(',', config('constants.extensions.image')),
            ],
        ];
    }
}
