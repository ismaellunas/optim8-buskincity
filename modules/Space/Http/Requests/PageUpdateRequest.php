<?php

namespace Modules\Space\Http\Requests;

use App\Http\Requests\PageRequest as AppPageRequest;

class PageUpdateRequest extends AppPageRequest
{
    public function authorize()
    {
        $user = $this->user();
        $space = $this->route('space');
        $page = $this->route('page');

        if (
            $user->can('managePage', Space::class)
            && $space
            && $page
        ) {
            return (
                parent::authorize()
                && $space->page_id == $page->id
                && $this->user()->can('update', $space)
            );
        }

        return false;
    }
}
