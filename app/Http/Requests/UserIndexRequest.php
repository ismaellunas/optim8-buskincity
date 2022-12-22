<?php

namespace App\Http\Requests;

use App\Services\UserService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserIndexRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        $roleIds = collect(app(UserService::class)->getRoleOptions())
            ->pluck('id')
            ->toArray();

        return [
            'term' => [
                'nullable',
                'max: 1024'
            ],
            'roles' => [
                'sometimes',
                'array'
            ],
            'roles.*' => [
                'numeric',
                Rule::in($roleIds),
            ]
        ];
    }
}
