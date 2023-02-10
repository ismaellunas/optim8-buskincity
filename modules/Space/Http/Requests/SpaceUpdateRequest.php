<?php

namespace Modules\Space\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Space\Services\SpaceService;

class SpaceUpdateRequest extends SpaceStoreRequest
{
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
        }
    }

    public function authorize()
    {
        return auth()->user()->can('update', $this->route('space'));
    }
}
