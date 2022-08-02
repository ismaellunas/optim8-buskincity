<?php

namespace Modules\Space\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Space\Entities\Space;
use Modules\Space\ModuleService;
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
        $user = auth()->user();

        $types = collect(app(SpaceService::class)->types())
            ->map(function ($type) {
                return $type->id;
            })
            ->all();

        $maxLengths = ModuleService::maxLengths();

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
            'type_id' => [
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
            'translations' => [
                'array'
            ],
            'translations.*.description' => [
                'nullable',
                'max:'.$maxLengths['description'],
            ],
            'translations.*.excerpt' => [
                'nullable',
                'max:'.$maxLengths['excerpt'],
            ],
            'translations.*.condition' => [
                'nullable',
                'max:'.$maxLengths['condition'],
            ],
            'translations.*.surface' => [
                'nullable',
                'max:'.$maxLengths['surface'],
            ],
        ];

        $routeName = request()->route()->getName();

        if ($routeName == 'admin.spaces.store') {
            $rules['parent_id'] = [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) use ($user) {
                    $space = Space::find($value);

                    if (! $user->can('create', $space, Space::class)) {
                        $fail(__('The Parent is invalid.'));
                    }
                },
            ];
        }

        if ($user->can('managePage', Space::class)) {
            $rules['is_page_enabled'] = [
                'boolean',
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
