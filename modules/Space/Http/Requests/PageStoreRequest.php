<?php

namespace Modules\Space\Http\Requests;

use App\Http\Requests\PageRequest as AppPageRequest;

class PageStoreRequest extends AppPageRequest
{
    public function authorize()
    {
        $user = $this->user();
        $space = $this->route('space');

        if ($space) {
            return (
                parent::authorize()
                && !$space->page_id
                && $user->can('create', $space)
            );
        }

        return false;
    }
}
