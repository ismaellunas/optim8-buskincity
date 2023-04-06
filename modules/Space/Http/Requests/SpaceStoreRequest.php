<?php

namespace Modules\Space\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Space\Entities\Space;
use Modules\Space\ModuleService;
use Modules\Space\Services\SpaceService;

class SpaceStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = auth()->user();
        $spaceService = app(SpaceService::class);

        $types = collect($spaceService->types())
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
                'exists:media,id',
            ],
            'cover' => [
                'nullable',
                'exists:media,id',
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

        if ($user->can('managePage', Space::class)) {
            $rules['is_page_enabled'] = [ 'boolean' ];
        }

        $this->additionalRules($rules);

        return $rules;
    }

    protected function additionalRules(&$rules): void
    {
        $user = auth()->user();
        $spaceService = app(SpaceService::class);

        $managedSpaces = null;

        $rules['parent_id'] = ['integer'];

        if ($user->can('space.add')) {

            $rules['parent_id'][] = 'nullable';

        } else {

            $rules['parent_id'][] = 'required';

            $managedSpaces = $user->spaces;
        }

        $rules['parent_id'][] = Rule::in(
            $spaceService->parentOptions($managedSpaces)->pluck('id')
        );
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('create', Space::class);
    }
}
