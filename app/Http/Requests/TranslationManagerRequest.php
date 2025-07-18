<?php

namespace App\Http\Requests;

use App\Services\TranslationManagerService;
use Illuminate\Validation\Rule;

class TranslationManagerRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $groups = app(TranslationManagerService::class)->getGroups($this->get('module'));

        return [
            'groups' => [
                'nullable',
                'array',
            ],
            'groups.*' => [
                Rule::in($groups),
            ],
        ];
    }
}
