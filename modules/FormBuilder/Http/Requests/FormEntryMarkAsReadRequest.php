<?php

namespace Modules\FormBuilder\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FormEntryMarkAsReadRequest extends FormRequest
{
    public function rules()
    {
        if (!$this->route('form_entry')) {
            return [
                'entries' => ['array'],
                'entries.*' => ['integer'],
            ];
        }

        return [];
    }

    public function authorize()
    {
        return auth()->user()->can('update', $this->route('form_builder'));
    }
}
