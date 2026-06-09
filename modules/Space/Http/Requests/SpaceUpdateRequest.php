<?php

namespace Modules\Space\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Space\Services\SpaceService;

class SpaceUpdateRequest extends SpaceStoreRequest
{
    protected function prepareForValidation(): void
    {
        $space = $this->route('space');
        $user = auth()->user();

        // Parent is read-only in the UI for scoped admins; ensure it is still validated.
        if ($space && ! $user->can('changeParent', $space)) {
            $this->merge([
                'parent_id' => $space->parent_id,
            ]);
        }

        parent::prepareForValidation();
    }

    protected function additionalRules(&$rules): void
    {
        $user = auth()->user();
        $space = $this->route('space');
        $spaceService = app(SpaceService::class);

        if ($user->can('space.edit')) {

            $rules['parent_id'] = ['nullable'];

            $options = $spaceService->parentOptionsFor($space);

            $rules['parent_id'][] = Rule::in($options->pluck('id'));

        } elseif ($user->can('changeParent', $space)) {
            $parentOptions = $spaceService->parentOptionsFor(
                $space,
                $user->spaces
            );

            $rules['parent_id'] = [
                'required',
                Rule::in($parentOptions->pluck('id'))
            ];
        } else {
            $rules['parent_id'] = [
                'required',
                'integer',
                Rule::in(array_filter([$space->parent_id])),
            ];
        }
    }

    public function authorize()
    {
        return auth()->user()->can('update', $this->route('space'));
    }
}
