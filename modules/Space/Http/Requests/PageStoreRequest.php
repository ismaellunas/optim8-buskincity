<?php

namespace Modules\Space\Http\Requests;

use App\Http\Requests\PageRequest as AppPageRequest;
use Modules\Space\Entities\Space;

class PageStoreRequest extends AppPageRequest
{
    public function authorize()
    {
        $user = $this->user();
        $space = $this->route('space');

        if (
            $user->can('managePage', Space::class)
            && $space
        ) {
            return (
                parent::authorize()
                && !$space->page_id
                && $user->can('create', $space)
            );
        }

        return false;
    }
}
