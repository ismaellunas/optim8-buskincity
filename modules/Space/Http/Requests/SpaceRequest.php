<?php

namespace Modules\Space\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Space\Entities\Space;

class SpaceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'max:128',
            ],
            'latitude' => [
                'nullable',
                'numeric',
            ],
            'longitude' => [
                'nullable',
                'numeric',
            ],
            'address' => [
                'nullable',
                'max:500',
            ],
            'parent_id' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();
                    $space = Space::find($value);

                    if (! $user->can('manage', $space, Space::class)) {
                        $fail(__('The Parent is invalid.'));
                    }
                },
            ]
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
