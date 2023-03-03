<?php

namespace Modules\FormBuilder\Http\Requests;

class FormEntryArchiveRequest extends FormEntryMarkAsReadRequest
{
    public function authorize()
    {
        return auth()->user()->can('delete', $this->route('form_builder'));
    }
}
