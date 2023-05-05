<?php

namespace Modules\FormBuilder\Http\Requests;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;
use Modules\FormBuilder\Services\AutomateUserCreationService;

class AutomateUserCreationRequest extends BaseFormRequest
{
    public function authorize()
    {
        return auth()->user()->can('form_builder.automate_user_creation');
    }

    public function rules()
    {
        $automateUserCreationService = app(AutomateUserCreationService::class);

        return [
            'email' => [
                'required',
            ],
            'last_name' => [
                'required',
            ],
            'first_name' => [
                'required',
            ],
            'mapping_rules' => [
                'array',
            ],
            'role' => [
                'nullable',
                Rule::in(
                    $automateUserCreationService->getRoleOptions()->pluck('id')
                )
            ]
        ];
    }
}
