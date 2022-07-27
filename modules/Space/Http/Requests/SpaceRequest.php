<?php

namespace Modules\Space\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Space\Entities\Space;
use Modules\Space\Services\SpaceService;

class SpaceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $types = collect(app(SpaceService::class)->types())
            ->keys()
            ->all();

        $rules = [
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
            'type' => [
                'nullable',
                'integer',
                Rule::in($types)
            ],
            'contacts' => [
                'nullable',
            ],
            'contacts.*.name' => [
                'required',
                'max:128',
            ],
            'contacts.*.email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'contacts.*.phone.number' => [
                'nullable',
                'phone:contacts.*.phone.country',
            ],
            'contacts.*.phone.country' => [
                'required_with:contacts.*.phone.number',
            ],
            'logo' => [
                'nullable',
                'file',
                'max:'.config('constants.one_megabyte') * 5,
                'mimes:'.implode(',', config('constants.extensions.image')),
            ],
            'cover' => [
                'nullable',
                'file',
                'max:'.config('constants.one_megabyte') * 50,
                'mimes:'.implode(',', config('constants.extensions.image')),
            ],
            'deleted_media' => [
                'nullable',
                'array'
            ],
        ];

        $routeName = request()->route()->getName();

        if ($routeName == 'admin.spaces.store') {
            $rules['parent_id'] = [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    $space = Space::find($value);
                    $user = auth()->user();

                    if (! $user->can('create', $space, Space::class)) {
                        $fail(__('The Parent is invalid.'));
                    }
                },
            ];
        }

        return $rules;
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
